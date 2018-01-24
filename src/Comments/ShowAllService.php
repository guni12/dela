<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;

/**
 * Form to update an item.
 */
class ShowAllService
{
    /**
    * @var array $comments, all comments.
    */
    protected $comments;
    protected $sess;
    protected $users;
    protected $user;
    protected $userController;
    protected $isadmin;
    protected $di;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->comments = $this->getAll();
        $session = $this->di->get("session");
        $this->sess = $session->get("user");
        $addsess = isset($this->sess) ? $this->sess : null;
        $this->sess = $addsess;
        $this->userController = $this->di->get("userController");
        $this->users = $this->userController->getAllUsers();
        $this->user = $this->userController->getOne($this->sess['id']);
        $this->isadmin = $this->sess['isadmin'] === 1 ? true : false;
    }

    /**
     * Get details on all comments.
     *
     * @return Comm
     */
    public function getAll()
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findAll();
    }


    /**
     * Sets the callable to use for creating routes.
     *
     * @param callable $urlCreate to create framework urls.
     *
     * @return void
     */
    public function setUrlCreator($route)
    {
        $url = $this->di->get("url");
        return call_user_func([$url, "create"], $route);
    }


    /**
     * Returns link for gravatar img
     *
     * @param object $item
     *
     * @return string htmlcode
     */
    public function getGravatar($item)
    {
        $comm = new Comm();
        $gravatar = $comm->getGravatar($item);
        return '<img src="' . $gravatar . '" alt=""/>';
    }


    /**
     * Returns when created or updated
     *
     * @param object $item
     * @return string htmlcode
     */
    public function getWhen($item)
    {
        $when = "";
        if ($item->updated) {
            $when .= '<span class="smaller">Ändrad: ' . $item->updated;
        } else {
            $when .= '<span class="smaller">Frågad: ' . $item->created;
        }
        return $when;
    }


    /**
     * Returns correct loginlink
     *
     * @param string $create
     * @param string $del
     *
     * @return string htmlcode
     */
    public function getLoginLink($create, $del)
    {
        $loggedin = '<a href="user/login">Logga in om du vill kommentera</a>';
        if ($this->sess['id']) {
            $loggedin = ' <a href="' . $create .'">Skriv ett inlägg</a>';
            if ($this->isadmin === true) {
                $loggedin .= ' | <a href="' . $del . '">Ta bort ett inlägg</a>';
            }
        }
        return $loggedin;
    }


        /**
     * Get details on item to load form with.
     *
     * @param string $where
     * @param array $params get details on item with id parentid.
     *
     * @return Comm
     */
    public function getParentDetails($where, $params)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findAllWhere($where, $params);
    }



    /**
    * @param obj $item - current comment
    * @param string $viewone - path
    * @param array $numbers - counted points, answers and comments
    * @param string $when - when comment was created
    * @param string $email
    *
    * @return string $html
    */
    public function getTheText($item, $numbers, $when, $email)
    {
        $gravatar = $this->getGravatar($email);
        $showid = $this->isadmin === true ? '(' . $item->id . '): ' : "";
        $viewone = $this->setUrlCreator("comm/view-one");
        $answers = $numbers[0];
        $comments = $numbers[1];
        $points = $numbers[2];

        $html = '<div class="clearfix"><h4><a href="' . $viewone . '/' . $item->id . '">';
        $html .= $showid . ' ' . $item->title . '</a><span class = "smaller"> ' . $answers . $comments . $points . '</span></h4><p>';
        $html .= $gravatar . '<span class="move20 by">' . $when . ' ' . $email . ' </span></p></div><hr class="border" />';
        return $html;
    }


    /**
     * Returns html for each item
     *
     * @param object $item
     * @param string $viewone
     *
     * @return string htmlcode
     */
    public function getValHtml(Comm $item, $email)
    {
        $answersct = 0;
        $commentsct = 0;
        $answers = "";
        $comments = "";

        $when = $this->getWhen($item);

        $commcomments = $this->getParentDetails("parentid = ?", $item->id);

        foreach ($commcomments as $key => $value) {
            $commentsct = $value->iscomment > 0 ? $commentsct + 1 : $commentsct;
            $answersct = $value->iscomment <= 0 ? $answersct + 1 : $answersct;
        }
        
        if ($answersct > 0) {
            $answers = $answersct . ' svar';
            $answers .= $commentsct > 0 ? ", " : "";
            $comments = $commentsct > 0 ? $commentsct . " kommentarer" : "";
        }

        $points = $answersct > 0 ? ", " : "";
        $points .= $item->points !== null && $item->points > 0 ? 'rank: ' . $item->points : "";
        $numbers = [$answers, $comments, $points];

        return $this->getTheText($item, $numbers, $when, $email);
    }


    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $create = $this->setUrlCreator("comm/create");
        $del = $this->setUrlCreator("comm/admindelete");

        $loggedin = $this->getLoginLink($create, $del);

        $html = '<div class="col-lg-10 col-sm-12 col-xs-12"><div class="movesome">

        <h3>Gruppinlägg <span class="small">' . $loggedin . '</span></h3>
        <hr />';

        foreach ($this->comments as $value) {
            if ((int)$value->parentid > 0) {
                continue;
            }
            $curruser = $this->userController->getOne($value->userid);
            $html .= $this->getValHtml($value, $curruser['email']);
        }
        
        $html .= '</div></div>';
        return $html;
    }
}
