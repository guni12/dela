<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\User\UserHelp;

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
    protected $isadmin;
    protected $elcar;
    protected $safety;
    protected $light;
    protected $heat;
    protected $di;
    protected $misc;
    protected $userhelp;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->initiate($di);
    }


    /**
    * Initiates some global variables
    */
    public function initiate($di)
    {
        $this->misc = new Misc($di);
        $this->userhelp = new UserHelp($di);

        $this->comments = $this->misc->getAll();
        $session = $this->di->get("session");
        $this->sess = $session->get("user");
        $this->sess = isset($this->sess) ? $this->sess : null;
        
        $this->users = $this->userhelp->getAllUsers();
        $this->user = $this->userhelp->getOne($this->sess['id']);
        $this->isadmin = $this->sess['isadmin'] === 1 ? true : false;
        $this->elcar = 0;
        $this->safety = 0;
        $this->light = 0;
        $this->heat = 0;
    }



    /**
     * Returns html for each item
     *
     * @param object $item
     * @param string $viewone - path
     *
     * @return string htmlcode
     */
    public function getValHtml(Comm $item, $viewone, $arr)
    {
        $showid = "";
        $curruser = $this->userhelp->getOne($item->userid);
        $email = $curruser['email'];
        $gravatar = $this->misc->getGravatar($email);
        if ($this->isadmin === true) {
            $showid = '(' . $item->id . '): ';
        }
        $title = '<a href="' . $viewone . '/' . $item->id . '">' . $showid . ' ' . $item->title . '</a>';
        $when = '<span class="smaller em06">' . $this->misc->getWhen($item) . '</span>';

        $html = '<tr><td class = "indgrav">' . $gravatar . '</td><td class = "indauthor">' . $curruser['acronym'] . '</td><td class = "latest">' . $title . '</td><td class = "when">' . $when . '</td><td class = "itis">' . $arr['gravatar'] . '</td><td class = "eager">' . $arr['acronym'] . '</td><td class = "number">' . $arr['count'] . '</td></tr>';
        return $html;
    }







    /**
    * return members that has made comments
    */
    public function getActives()
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $sql = 'SELECT userid,COUNT(*) as count FROM `comm` GROUP BY userid ORDER BY count DESC ';
        return $comm->findSql($sql);
    }


    /**
     * Returns json_decoded title and text
     * @param object $item
     * @return string htmlcode
     */
    public function getDecode($item)
    {
        $comt = json_decode($item);
        if ($comt->frontmatter->tags) {
            return is_array($comt->frontmatter->tags) ? $comt->frontmatter->tags : [];
        }
    }


    /**
    * @param obj $value - comment with tag perhaps
    */
    public function countTags($value)
    {
        $test = $this->getDecode($value->comment);

        if (count($test) > 3) {
            $this->elcar = $test[0] == "elcar" ? $this->elcar + 1 : $this->elcar;
            $this->safety = $test[1] == "safety" ? $this->safety + 1 : $this->safety;
            $this->light = $test[2] == "light" ? $this->light + 1 : $this->light;
            $this->heat = $test[3] == "heat" ? $this->heat + 1 : $this->heat;
        }
    }



    /**
     * Returns list of taginfo
     * 
     * @param object $comments
     * @return array
     */
    public function getTagarr()
    {
        foreach ($this->comments as $value) {
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
    public function getTaginfo($base)
    {
        $arr = $this->getTagarr();
        $html = '';
        foreach ($arr as $key => $value) {
            $html .= '<span class="tagsquare"><a href = "' . $base . '/' . $key . '">' . $value[1] . ' <span class="tagsize em06">[ ' . $value[0] . ' ]</span></a></span>';
        }
        return $html;
    }



    /**
    * @param string $viewone - path
    * @return string $html - htmltext for the questions
    */
    public function getLatestQuestions($viewone)
    {
        usort($this->comments, array($this->misc, "dateSort"));
        $count = 0;
        $reversed = array_reverse($this->comments);
        $html = "";
        $test = $this->getActivesInfo($viewone);

        foreach ($reversed as $key => $value) {
            $html .= ((int)$value->parentid <= 0) && $count < 5 ? $this->getValHtml($value, $viewone, $test[$count]) : "";
            $count = ((int)$value->parentid <= 0) ? $count + 1 : $count;
        }
        return $html;
    }


    /**
    * @return string $html - htmltext for the actives
    */
    public function getActivesInfo($viewone)
    {
        $actives = $this->getActives();
        $arr = [];

        $count = 0;

        foreach ($actives as $key => $val) {
            if ($count >= 5) {
                break;
            }
            $one = $this->userhelp->getOne($val->userid);
            $item = $this->misc->getUsersHtml($one, $viewone);
            $test['gravatar'] = $item['gravatar'];
            $test['acronym'] = $item['acronym'];
            $test['count'] = $val->count;
            $arr[$key] = $test;
            $count += 1;
        }
        return $arr;
    }



    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $html = "";

        $viewone = $this->misc->setUrlCreator("comm/view-one");
        $base = $this->misc->setUrlCreator("comm/tags/");

        $html .= '<div class="col-lg-12 col-sm-12 col-xs-12">';
        $html .= $this->getTaginfo($base);

        $html .= '<table class = "indexmember tagpage font20"><tbody><tr>';
        $html .= '<th class = "indgrav"></th>';
        $html .= '<th class = "indauthor"></th>';
        $html .= '<th class = "latest">Senast</th>';
        $html .= '<th class = "when">Skrevs</th>';
        $html .= '<th class = "itis"></th>';
        $html .= '<th class = "eager">Flitigast</th>';
        $html .= '<th class = "number">Inlägg</th>';
        $html .= '</tr>';
        $html .= $this->getLatestQuestions($viewone);
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
