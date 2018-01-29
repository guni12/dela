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
    protected $answersct;
    protected $commentsct;

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
        $this->answersct = 0;
        $this->commentsct = 0;
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
            $when .= '<span class="smaller">' . $item->created;
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
    public function getTheText($item, $numbers, $when, $user)
    {
        $gravatar = $this->getGravatar($user[0]);
        $showid = $this->isadmin === true ? '(' . $item->id . '): ' : "";
        $viewone = $this->setUrlCreator("comm/view-one");
        $answers = $numbers[0];
        $comments = $numbers[1];
        $points = $numbers[2];

        $title = '<a href="' . $viewone . '/' . $item->id . '">';
        $title .= $showid . ' ' . $item->title . '</a>';

        $html = '<tr><td class = "allmember">' . $gravatar . ' ' . $user[1] . '</td>';
        $html .= '<td class = "alltitle">' . $title . '</td>';
        $html .= '<td class = "asked">' . $when . '</td>';
        $html .= '<td = "respons"><span class = "smaller">' . $answers . $comments . $points . '</span></td>';
        $html .= '</tr>';
        return $html;
    }


    
    /**
    * @param object $commcomments - comments to at parentcomment
    */
    public function countResponses($commcomments)
    {
        foreach ($commcomments as $value) {
            $this->commentsct = $value->iscomment > 0 ? $this->commentsct + 1 : $this->commentsct;
            $this->answersct = $value->iscomment <= 0 ? $this->answersct + 1 : $this->answersct;
        }
    }


    /**
     * Returns html for each item
     *
     * @param object $item
     * @param string $viewone
     *
     * @return string htmlcode
     */
    public function getValHtml(Comm $item, $email, $acronym)
    {
        $answers = "";
        $comments = "";

        $when = $this->getWhen($item);
        $commcomments = $this->getParentDetails("parentid = ?", $item->id);

        $this->countResponses($commcomments);

        
        if ($this->answersct > 0) {
            $answers = $this->answersct . ' svar';
            $answers .= $this->commentsct > 0 || $item->points > 0 ? ", " : "";
            $comments = $this->commentsct > 0 ? $this->commentsct . " kommentarer" : "";
        }

        $comma = $this->commentsct > 0 ? ", " : "";
        $points = $item->points !== null && $item->points > 0 ? $comma . 'rank: ' . $item->points : "";
        $numbers = [$answers, $comments, $points];
        $user = [$email, $acronym];

        return $this->getTheText($item, $numbers, $when, $user);
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

        $html = '<div class="col-lg-12 col-sm-12 col-xs-12"><div class="">

        <h3>Gruppinlägg <span class="small">' . $loggedin . '</span></h3>
        <hr />';

        $html .= '<table class = "member tagpage"><tr><th class="allmember">Medlem</th><th class="alltitle">Rubrik</th><th class="asked">Frågad</th><th class="respons">Respons</th></tr>';

        foreach ($this->comments as $value) {
            if ((int)$value->parentid > 0) {
                continue;
            }
            $curruser = $this->userController->getOne($value->userid);
            $html .= $this->getValHtml($value, $curruser['email'], $curruser['acronym']);
        }
        $html .= '</table>';
        $html .= '</div></div>';
        return $html;
    }
}
