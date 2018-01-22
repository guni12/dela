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
    protected $elcar;
    protected $safety;
    protected $light;
    protected $heat;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->initiate();
    }


    /**
    * Initiates some global variables
    */
    public function initiate()
    {
        $this->comments = $this->getAll();
        $session = $this->di->get("session");
        $this->sess = $session->get("user");
        $addsess = isset($this->sess) ? $this->sess : null;
        $this->sess = $addsess;
        $this->userController = $this->di->get("userController");
        $this->users = $this->userController->getAllUsers();
        $this->user = $this->userController->getOne($this->sess['id']);
        $this->isadmin = $this->sess['isadmin'] === 1 ? true : false;
        $this->elcar = 0;
        $this->safety = 0;
        $this->light = 0;
        $this->heat = 0;
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
    *
    */
    public function isUpdated($item)
    {
        if ($item->parentid !== null) {
            return 'Svar: ' . $item->created . ', Ändrad: ' . $item->updated;
        } else {
            return 'Fråga: ' . $item->created . ', Ändrad: ' . $item->updated;
        }
    }



    /**
    *
    */
    public function isNotUpdated($item)
    {
        if ($item->parentid !== null) {
            return 'Svar: ' . $item->created;
        } else {
            return 'Fråga: ' . $item->created;
        }
    }



    /**
     * Returns when created or updated
     *
     * @param object $item
     * @return string htmlcode
     */
    public function getWhen($item)
    {
        $when = $item->updated ? $this->isUpdated($item) : $this->isNotUpdated($item);
        return $when;
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
        //$when = $item->created;
        $when = $this->getWhen($item);
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
        //var_dump($item);
        $comt = json_decode($item);
        $tags = [];
        if ($comt->frontmatter->tags) {
            if (is_array($comt->frontmatter->tags)) {
                return $comt->frontmatter->tags;
            }
        }
    }


    /**
    * @param obj $value - comment with tag perhaps
    */
    public function countTags($value)
    {
        $test = $this->getDecode($value->comment);

        if ($test[0] == "elcar") {
            $this->elcar += 1;
        }
        if ($test[1] == "safety") {
            $this->safety += 1;
        }
        if ($test[2] == "light") {
            $this->light += 1;
        }
        if ($test[0] == "heat") {
            $this->heat += 1;
        }
    }



    /**
     * Returns list of taginfo
     * 
     * @param object $comments
     * @return array
     */
    public function getTagarr() {
        foreach ($this->comments as $key => $value) {
            $this->countTags($value);
        }

        $arr['elcar'] = [$this->elcar, "Elbil"];
        $arr['safety'] = [$this->safety, "Säkerhet"];
        $arr['light'] = [$this->light, "Belysning"];
        $arr['heat'] = [$this->heat, "Värme"];

        arsort($arr);

        return $arr;
    }


    /**
    * @return string $html - htmltext for the tags
    */
    public function getTaginfo($base) {
        $arr = $this->getTagarr();
        $html = '<h4>';
        foreach ($arr as $key => $value) {
            $html .= '<a href = "' . $base . '/' . $key . '">' . $value[1] . '<span class="tagsize">[ ' . $value[0] . ' ]</span></a>  ';
        }
        $html .= '</h4><hr class="border" />';
        return $html;
    }



    /**
    * @param string $viewone - path
    * @return string $html - htmltext for the questions
    */
    public function getLatestQuestions($viewone)
    {
        usort($this->comments, array($this, "dateSort"));
        $ct = 0;
        $reversed = array_reverse($this->comments);
        $html = "";
        //$value->parentid means it is respons to a parent

        foreach ($reversed as $value) {
            $html .= ((int)$value->parentid <= 0) && $ct < 5 ? $this->getValHtml($value, $viewone) : "";
            $ct = ((int)$value->parentid <= 0) ? $ct + 1 : $ct;
        }
        return $html;
    }


    /**
    * @return string $html - htmltext for the actives
    */
    public function getActivesInfo($viewone)
    {
        $actives = $this->getActives();
        $userController = $this->di->get("userController");
        $html = "";

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
        return $html;
    }



    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $html = "";

        $viewone = $this->setUrlCreator("comm/view-one");
        $base = $this->setUrlCreator("comm/tags/");

        $html .= '<div class="col-lg-6 col-sm-6 col-xs-12">';
        $html .= '<div class="margin-right">';
        $html .= '<h3>Senaste frågorna</h3><hr />';
        $html .= $this->getLatestQuestions($viewone);

        $html .= '</div></div><div class="col-lg-6 col-sm-6 col-xs-12">';
        $html .= '<h3>Våra Taggar</h3>';
        $html .= $this->getTaginfo($base);

        $html .= '<h3>Flitigaste användarna</h3><hr />';
        $html .= $this->getActivesInfo($viewone);

        $html .= "</div";

        return $html;
    }
}
