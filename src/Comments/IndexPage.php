<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;

/**
 * Helper for html-code
 */
class IndexPage
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
            if ($item->parentid !== null) {
                $when .= 'Svar: ' . $item->created . ', Ändrad: ' . $item->updated;
            } else {
                $when .= 'Fråga: ' . $item->created . ', Ändrad: ' . $item->updated;
            }
        } else {
            if ($item->parentid !== null) {
                $when .= 'Svar: ' . $item->created;
            } else {
                $when .= 'Fråga: ' . $item->created;
            }
        }
        return $when;
    }


    /**
     * Returns correct loginlink
     *
     * @param string $create - path
     * @param string $del - path
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
     * Returns html for each item
     *
     * @param object $item
     * @param string $viewone - path
     *
     * @return string htmlcode
     */
    public function getValHtml(Comm $item, $viewone)
    {
        $showid = "";
        $curruser = $this->userController->getOne($item->userid);
        $email = $curruser['email'];
        $gravatar = $this->getGravatar($email);
        //$when = $this->getWhen($item);
        $when = $item->created;
        if ($this->isadmin === true) {
            $showid = '(' . $item->id . '): ';
        }
        $html = '<div class="clearfix"><h4><a href="' . $viewone . '/' . $item->id . '">';
        $html .= $showid . ' ' . $item->title . '</a></h4><p class="smalltext">';
        $html .= $when . ' ' . $curruser['acronym'] . ' ' . $gravatar . '</p></div><hr class="border" />';
        return $html;
    }


    /**
     * Returns html for user item
     *
     * @param object $item
     * @param string $viewone - path
     *
     * @return string htmlcode
     */
    public function getUsersHtml($item, $viewone)
    {
        $showid = "";
        $viewone = $this->setUrlCreator("user/view-one") . "/";
        $gravatar = $this->getGravatar($item['email']);
        $html = '<div class="clearfix">';
        $html .= '<h4><a href="' . $viewone . $item['id'] . '">' . $gravatar . ' ' . $item['acronym'] . '</a></h4>';
        return $html;
    }

    /**
     * Sort list of dates
     *
     * @param string $a, $b - dates
     *
     * @return sorted dates
     */
    public function dateSort($a, $b)
    {
        return strtotime($a->created) - strtotime($b->created);
    }


    public function getActives()
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $sql = 'SELECT userid,COUNT(*) as count FROM `comm` GROUP BY userid ORDER BY count DESC ';
        return $comm->findSql($sql);
    }


    /**
     * Returns json_decoded title and text
     * If lead text, headline is larger font
     * @param object $item
     * @return string htmlcode
     */
    public function getDecode($item, $lead = null)
    {
        $comt = json_decode($item);
        $tags = [];
        if ($comt->frontmatter->tags) {
            if (is_array($comt->frontmatter->tags)) {
                return $comt->frontmatter->tags;
            }
        }
    }


    /**
     * Returns list of taginfo
     * 
     * @param object $comments
     * @return array
     */
    public function getTaginfo($comments) {
        $elcar = 0;
        $safety = 0;
        $light = 0;
        $heat = 0;

        foreach ($comments as $key => $value) {
            $test = $this->getDecode($value->comment);

            if ($test[0] == "elcar") {
                $elcar += 1;
            }
            if ($test[1] == "safety") {
                $safety += 1;
            }
            if ($test[2] == "light") {
                $light += 1;
            }
            if ($test[0] == "heat") {
                $heat += 1;
            }
        }

        $arr['elcar'] = [$elcar, "Elbil"];
        $arr['safety'] = [$safety, "Säkerhet"];
        $arr['light'] = [$light, "Belysning"];
        $arr['heat'] = [$heat, "Värme"];

        arsort($arr);

        return $arr;
    }


    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $loggedin = "";
        $html = "";

        $create = $this->setUrlCreator("comm/create");
        $del = $this->setUrlCreator("comm/admindelete");
        $viewone = $this->setUrlCreator("comm/view-one");
        $base = $this->setUrlCreator("comm/tags/");
        $loggedin = $this->getLoginLink($create, $del);

        $html .= '<div class="col-lg-6 col-sm-6 col-xs-12">';
        $html .= '<div class="margin-right">';
        $html .= '<h3>Senaste frågorna</h3><hr />';

        usort($this->comments, array($this, "dateSort"));

        $ct = 0;
        //var_dump($this->comments);
        $reversed = array_reverse($this->comments);

        foreach ($reversed as $value) {
            if ((int)$value->parentid > 0) {
                continue;
            }
            if ($ct >= 5) {
                break;
            }
            $html .= $this->getValHtml($value, $viewone);
            $ct += 1;
        }

        /*$html .= '<h3>Senaste svaren</h3><hr />';

        $count = 0;

        foreach ($this->comments as $value) {
            if ((int)$value->parentid > 0) {
                $html .= $this->getValHtml($value, $viewone);
                $count += 1;
            }
            if ($count >= 5) {
                break;
            }
        }*/


        $html .= '</div></div><div class="col-lg-6 col-sm-6 col-xs-12">';

                $html .= '<h3>Våra Taggar</h3>';

        $arr = $this->getTaginfo($this->comments);
        $html .= '<h4>';

        foreach ($arr as $key => $value) {
            $html .= '<a href = "' . $base . '/' . $key . '">' . $value[1] . '<span class="tagsize">[ ' . $value[0] . ' ]</span></a>  ';
        }
        $html .= '</h4><hr class="border" />';

        $html .= '<h3>Flitigaste användarna</h3><hr />';

        $actives = $this->getActives();

        $userController = $this->di->get("userController");

        $count = 0;

        foreach ($actives as $val) {
            if ($count >= 5) {
                break;
            }
            $one = $userController->getOne($val->userid);
            $html .= $this->getUsersHtml($one, $viewone);
            $html .= "<p class='smalltext'>" . $val->count . " inlägg ";
            $html .=  '</div><hr class="border" />';
            $count += 1;
        }
        $html .= "</div";

        return $html;
    }
}
