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
        $this->sess = $session->get("user");
        $addsess = isset($this->sess) ? $this->sess : null;
        $this->sess = $addsess;
        $this->chosenid = $id;
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
            default:
                return "Värme";
        }
    }


    /**
     * @param object $item
     *
     * @return string - html-text of tags and their links
     */
    public function getTags($item)
    {
        //var_dump("getTags: ", $item);
        $base = $this->setUrlCreator("comm/tags/");
        $text = "";
        $taglinks = "";
        if (is_array($item)) {
            foreach ($item as $key => $val) {
                if ($val == null || $val == "null") {
                    continue;
                } else {
                    $taglinks .= '<a href="' . $base . "/" . $val . '">' . $this->getName($val) . "</a>, ";
                }
            }
        }
        $taglinks = substr($taglinks, 0, -2);
        $text .=  $taglinks;
        return $text;
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
        $text .= "</td>";
        $text .= "</tr>";

        return $text;
    }


    /**
     * @param object $item
     * @param string $viewone - linkbase to commentpage
     *
     * @return string - html-text for the comments
     */
    public function getCommentHTML($item, $viewone, $comm)
    {
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
        $text .= "<td class = 'parenttag'>" . $tag . "</td>";
        $text .= "<td></td>";
        $text .= "</tr>";
        return $text;
    }


    /**
     * @param object $item
     * @param string $viewone - linkbase to commentpage
     *
     * @return string - html-text for the answers
     */
    public function getAnswersHTML($item, $viewone, $comm)
    {
        $parent = $comm->findOne($item['isanswer']);
        $color = "";
        if ($parent->parentid == null) {
            $color = "delared";
        } else {
            $color = "delablue";
        }
        $decodeMarkdown = json_decode($parent->comment);
        $tag = $this->getTags($decodeMarkdown->frontmatter->tags);
        //var_dump($tag);
        $parenttitel = $parent->title;

        $text = "<td><a href='" . $viewone . "/" . $item['id'] . "'><span class='delablue'>" . $item['comm']->frontmatter->title . "</span></a></td>";
        $text .= "<td></td>";
        $text .= "<td class = 'parent'><a href='" . $viewone . "/" . $parent->id . "'><span class='" . $color . "'>"  . $parent->title . "</span></a></td>";
        $text .= "<td class = 'parenttag'>" . $tag . "</td>";
        $text .= "<td></td>";
        $text .= "</tr>";

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

        $comm = $this->di->get("commController");
        $grav = $comm->getGravatar($this->person->email, 50);

        $comments = $this->getUserComments();

        $reputation = 0;
        $text = "";

        if ($comments) {
            $array = $this->populateArray($comments);
            $virgin = false;
        }

        $text .= '<table class = "member tagpage"><tr>';
        $text .= '<th class = "title"><span class = "delared">Fråga</span> | <span class = "delablue">Svar</span> | <span class = "delagreen">Kommentar</span></th><th class = "tag">Taggar</th><th class = "parent">Till <span class = "delared">Fråga</span> | <span class = "delablue">Svar</span></th><th class = "parenttag">Taggar</th><th class = "answercomments">Har <span class = "delablue">Svar</span> | <span class = "delagreen">Kommentarer</span></th></tr>';

        foreach ($array as $key => $value) {
            $text .= '<tr>';

            if ($value['isanswer'] == null && $value['iscomment'] == null) {
                $text .= $this->getQuestionHTML($value, $viewone);

                $points = $value['points'] + 0.5;
                $questionValue = $points * 3;
                $reputation += $questionValue;
            }
            elseif ($value['iscomment']) {
                $text .= $this->getCommentHTML($value, $viewone, $comm);

                $points = $value['points'] + 0.5;
                $commentValue = $points * 2;
                $reputation += $commentValue;
            }
            elseif ($value['isanswer']) {
                $text .= $this->getAnswersHTML($value, $viewone, $comm);

                $points = $value['points'] + 0.5;
                $test = $this->getIsAccepted($value['id']);
                if ($test) {
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


        $html .= "<img src='" . $grav . "' />";
        $html .= '<h1>' . $this->person->acronym . '</h1>';
        $html .= $startinfo;
        $html .= $text;

        return $html;
    }
}
