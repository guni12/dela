<?php

namespace Guni\User;

use \Anax\DI\DIInterface;
use \Guni\User\User;
use \Guni\Comments\Comm;

/**
 * Form to update an item.
 */
class ShowOneService
{
    /**
    * @var array $comments, all comments.
    */
    protected $sess;
    protected $person;
    protected $chosenid;
    protected $comments;
    protected $grav;
    protected $comm;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di, $id)
    {
        $this->di = $di;
        $this->person = $this->getItems($id);
        $session = $this->di->get("session");
        $sess = $session->get("user");
        $this->sess = isset($sess) ? $sess : null;
        $this->chosenid = $id;
        $this->comments = $this->getUserComments();
        $comm = $this->di->get("commController");
        $this->grav = $comm->getGravatar($this->person->email, 50);
    }

    /**
     * Get details on comments.
     *
     *
     * @return All comments
     */
    public function getItems($id)
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        return $user->find("id", $id);
    }


    /**
     * Sets the callable to use for creating routes.
     *
     * @param callable $urlCreate to create framework urls.
     *
     * @return url to path
     */
    public function setUrlCreator($route)
    {
        $url = $this->di->get("url");
        return call_user_func([$url, "create"], $route);
    }


    /**
     * @param integer $id
     *
     * @return objects - where parent is id and not a comment
     */
    public function getAnswers($id)
    {
        $dbComm = new Comm();
        $dbComm->setDb($this->di->get("db"));
        $parentid = "parentid = ? AND iscomment < 1";
        return $dbComm->findAllWhere($parentid, $id);
    }


    /**
     * @param integer $comm - boolean if item is comment or not
     * @param string $parent - of parentnumber
     *
     * @return string - where parent is id and not a comment
     */
    public function getIsAnswer($parent, $comm)
    {
        if ($comm > 0) {
            return null;
        } else {
            return $parent;
        }
    }


    /**
     * @param integer $id
     *
     * @return objects - where parent is id and is a comment
     */
    public function getComments($id)
    {
        $dbComm = new Comm();
        $dbComm->setDb($this->di->get("db"));
        $parentid = "parentid = ? AND iscomment > 0";
        return $dbComm->findAllWhere($parentid, $id);
    }


    /**
     * @param integer $comm - boolean if item is comment or not
     * @param string $parent - of parentnumber
     *
     * @return string - where parent is id and is a comment
     */
    public function getIsComment($parent, $comm)
    {
        if ($comm > 0) {
            return $parent;
        } else {
            return null;
        }
    }


    /**
     * @param integer $id
     *
     * @return objects - where answer is accepted by questioner
     */
    public function getIsAccepted($id)
    {
        $dbComm = new Comm();
        $dbComm->setDb($this->di->get("db"));
        $accepted = "accept = ?";
        return $dbComm->findAllWhere($accepted, $id);
    }


    /**
     * @param string $eng - the tag name in english
     *
     * @return string - tag name in swedish
     */
    public function getName($eng)
    {
        switch ($eng) {
            case "elcar":
                return "Elbil";
                break;
            case "safety":
                return "Säkerhet";
                break;
            case "light":
                return "Belysning";
                break;
            case "heat":
                return "Värme";
        }
    }


    public function getTagLink($val)
    {
        $base = $this->setUrlCreator("comm/tags/");
        if ($val) {
            return '<a href="' . $base . "/" . $val . '">' . $this->getName($val) . "</a>, ";
        }
    }


    /**
     * @param object $item
     *
     * @return string - html-text of tags and their links
     */
    public function getTags($item)
    {
        $taglinks = "";
        if (is_array($item)) {
            foreach ($item as $key => $val) {
                $taglinks .= $this->getTagLink($val);
            }
        }
        $taglinks = substr($taglinks, 0, -2);
        return $taglinks;
    }


    /**
     * @param object $item
     * @param string $viewone - linkbase to commentpage
     *
     * @return string - html-text for the qustions
     */
    public function getQuestionHTML($item, $viewone) {
        $tag = $this->getTags($item['comm']->frontmatter->tags);
        $hasanswers = "";
        $hascomments = "";

        $text = "<td><a href='" . $viewone . "/" . $item['id'] . "'><span class='delared'>" . $item['comm']->frontmatter->title . "</span></a></td>";
        $text .= "<td class = 'tag'>" . $tag . "</td>";
        $text .= "<td></td>";
        $text .= "<td></td>";
        $text .= "<td class = 'answercomments'>";
        if ($item['hasanswer']) {
            $hasanswers = "<span class='delablue'> [" . count($item['hasanswer']) . " ] </span>";
        }
        if ($item['hascomments']) {
            $hascomments = "<span class='delagreen'> [" . count($item['hascomments']) . " ] </span>";
        }
        $text .= $hasanswers;
        $text .= $hascomments;
        $text .= "</td></tr>";

        return $text;
    }


    /**
     * @param object $item
     * @param string $viewone - linkbase to commentpage
     *
     * @return string - html-text for the comments
     */
    public function getCommentHTML($item, $viewone)
    {
        $comm = $this->di->get("commController");
        $parent = $comm->findOne($item['iscomment']);
        $color = "";
        if ($parent->parentid == null) { //Comment to question
            $color = "delared";
        } else {
            $color = "delablue"; // Comment to answer
        }
        $decodeMarkdown = json_decode($parent->comment);
        $tag = $this->getTags($decodeMarkdown->frontmatter->tags);
        $parenttitel = $parent->title;
        $text = "<td><a href='" . $viewone . "/" . $item['id'] . "'><span class='delagreen'>" . $item['comm']->frontmatter->title . "</span></a></td>";
        $text .= "<td></td>";
        $text .= "<td class = 'parent'><a href='" . $viewone . "/" . $parent->id . "'><span class='" . $color . "'>"  . $parent->title . "</span></a></td>";
        $text .= "<td class = 'parenttag'>" . $tag . "</td><td></td></tr>";
        return $text;
    }


    /**
     * @param object $item
     * @param string $viewone - linkbase to commentpage
     *
     * @return string - html-text for the answers
     */
    public function getAnswersHTML($item, $viewone)
    {
        $comm = $this->di->get("commController");
        $parent = $comm->findOne($item['isanswer']);
        $color = "";
        if ($parent->parentid == null) {
            $color = "delared";
        } else {
            $color = "delablue";
        }
        $decodeMarkdown = json_decode($parent->comment);
        $tag = $this->getTags($decodeMarkdown->frontmatter->tags);
        $parenttitel = $parent->title;

        $text = "<td><a href='" . $viewone . "/" . $item['id'] . "'><span class='delablue'>" . $item['comm']->frontmatter->title . "</span></a></td><td></td>";
        $text .= "<td class = 'parent'><a href='" . $viewone . "/" . $parent->id . "'><span class='" . $color . "'>"  . $parent->title . "</span></a></td>";
        $text .= "<td class = 'parenttag'>" . $tag . "</td><td></td></tr>";

        return $text;
    }


    /**
     * @param object $item
     * @param string $viewone - linkbase to commentpage
     *
     * @return string - html-text for the points
     */
    public function getPointsHTML($reputation)
    {
        $dbComm = new Comm();
        $dbComm->setDb($this->di->get("db"));

        $votedsql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE hasvoted LIKE "%' . $this->chosenid . '%"';

        $votedcount = $dbComm->findSql($votedsql)[0]->count;

        $questionssql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE userid = ' . $this->chosenid . ' AND parentid IS NULL';

        $questionscount = $dbComm->findSql($questionssql)[0]->count;

        $answersql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE userid = ' . $this->chosenid . ' AND iscomment = 0 AND parentid IS NOT NULL OR userid = ' . $this->chosenid . ' AND iscomment IS NULL AND parentid IS NOT NULL';

        $answercount = $dbComm->findSql($answersql)[0]->count;

        $commentsql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE userid = ' . $this->chosenid . ' AND iscomment = 1 AND parentid IS NOT NULL';

        $commentcount = $dbComm->findSql($commentsql)[0]->count;

        $pointssql = 'SELECT SUM(`points`) AS count FROM `comm` WHERE userid = ' . $this->chosenid;

        $pointcount = $dbComm->findSql($pointssql)[0]->count;

        return '<p>Rykte: ' . $reputation . ', Poäng: ' . $pointcount . ', ' . 'Röstat: ' . $votedcount . '<br />Frågor: ' . $questionscount . ', Svar: ' . $answercount . ', Kommentarer: ' . $commentcount . '</p>';
    }



    /**
     *
     * @return object - get all comments from chosen user
     */ 
    public function getUserComments()
    {
        $userid = "userid = ?";

        $dbComm = new Comm();
        $dbComm->setDb($this->di->get("db"));

        return $dbComm->findAllWhere($userid, $this->chosenid);
    }


    /**
     * @param object $comments
     *
     * @return array - commentitems for the userpage
     */    
    public function populateArray($comments)
    {
        foreach ($comments as $key => $val) {
            $obj = [];
            $obj['comm'] = json_decode($val->comment);
            $obj['isanswer'] = $this->getIsAnswer($val->parentid, $val->iscomment);
            $obj['iscomment'] = $this->getIsComment($val->parentid, $val->iscomment);
            $obj['id'] = $val->id;
            $obj['hasanswer'] = $this->getAnswers($obj['id']);
            $obj['hascomments'] = $this->getComments($obj['id']);
            $obj['points'] = $val->points;

            $array[$key] = $obj;
        }
        return $array;
    }

    /**
    *
    * @return string $text - html for tableintro
    */
    public function getTableHead()
    {
        $text = '<table class = "member tagpage"><tr>';
        $text .= '<th class = "title"><span class = "delared">Fråga</span> | <span class = "delablue">Svar</span> | <span class = "delagreen">Kommentar</span></th><th class = "tag">Taggar</th><th class = "parent">Till <span class = "delared">Fråga</span> | <span class = "delablue">Svar</span></th><th class = "parenttag">Taggar</th><th class = "answercomments">Har <span class = "delablue">Svar</span> | <span class = "delagreen">Kommentarer</span></th></tr>';
        return $text;
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
        $virgin = true;
        $array = [];

        $reputation = 0;
        $text = $this->getTableHead();

        if ($this->comments) {
            $array = $this->populateArray($this->comments);
            $virgin = false;
        }

        foreach ($array as $key => $value) {
            $text .= '<tr>';

            if ($value['isanswer'] == null && $value['iscomment'] == null) {
                $text .= $this->getQuestionHTML($value, $viewone);

                $points = $value['points'] + 0.5;
                $questionValue = $points * 3;
                $reputation += $questionValue;
            }
            elseif ($value['iscomment']) {
                $text .= $this->getCommentHTML($value, $viewone);

                $points = $value['points'] + 0.5;
                $commentValue = $points * 2;
                $reputation += $commentValue;
            }
            elseif ($value['isanswer']) {
                $text .= $this->getAnswersHTML($value, $viewone);

                $points = $value['points'] + 0.5;
                $bonus = $this->getIsAccepted($value['id']);
                if ($bonus) {
                    $answerValue = $points * 8;
                } else {
                    $answerValue = $points * 4;
                }
                $reputation += $answerValue;
            }
        }
        $text .= "</table><br />";


        if ($virgin == true) {
            $startinfo = $this->person->acronym . " har inte gjort några inlägg ännu.";
        } else {
            $startinfo = $this->getPointsHTML($reputation);
        }


        $html .= "<img src='" . $this->grav . "' />";
        $html .= '<h1>' . $this->person->acronym . '</h1>';
        $html .= $startinfo;
        $html .= $text;

        return $html;
    }
}
