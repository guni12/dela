<?php

namespace Guni\User;

use \Anax\DI\DIInterface;
use \Guni\User\User;
use \Guni\User\UserHelp;
use \Guni\Comments\Comm;
use \Guni\Comments\Misc;

/**
 * Form to update an item.
 */
class ShowOneService
{
    /**
    * @var array $comments, all comments.
    */
    protected $person;
    protected $chosenid;
    protected $comments;
    protected $di;
    protected $misc;
    protected $help;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di, $id)
    {
        $this->di = $di;
        $this->misc = new Misc($di);
        $this->help = new UserHelp($di);
        $this->person = $this->help->getUserItems($id);
        $this->chosenid = $id;
        $this->comments = $this->misc->findAllWhere("userid = ?", $id);
    }


    /**
    *
    * @return string htmltext with link
    */
    public function getTagLink($val)
    {
        $base = $this->misc->setUrlCreator("comm/tags/");
        if ($val) {
            return '<a href="' . $base . "/" . $val . '">' . $this->help->getName($val) . "</a>, ";
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
            foreach ($item as $val) {
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
    public function getQuestionHTML($item, $viewone)
    {
        $tag = $this->getTags($item['comm']->frontmatter->tags);
        $hasanswers = "";
        $hascomments = "";

        $text = "<td class = 'title'><a href='" . $viewone . "/" . $item['id'] . "'><span class='delared'>" . $item['comm']->frontmatter->title . "</span></a></td>";
        $text .= "<td class = 'tag em06'>" . $tag . "</td>";
        $text .= "<td class = 'parent em08'></td>";
        $text .= "<td class = 'parenttag em06'></td>";
        $text .= "<td class = 'answercomments em08'>";
        if ($item['hasanswer']) {
            $hasanswers = "<span class='delablue'> [" . count($item['hasanswer']) . "] </span>";
        }
        if ($item['hascomments']) {
            $hascomments = "<span class='delagreen'> [" . count($item['hascomments']) . "] </span>";
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
        $parent = $this->misc->getItemDetails($item['iscomment']);
        if ($parent->parentid == null) { //Comment to question
            $color = "delared";
        } else {
            $color = "delablue"; // Comment to answer
        }
        $decodeMarkdown = json_decode($parent->comment);
        $tag = $this->getTags($decodeMarkdown->frontmatter->tags);
        $text = "<td class = 'title'><a href='" . $viewone . "/" . $item['id'] . "'><span class='delagreen'>" . $item['comm']->frontmatter->title . "</span></a></td>";
        $text .= "<td class = 'tag em06'></td>";
        $text .= "<td class = 'parent em08'><a href='" . $viewone . "/" . $parent->id . "'><span class='" . $color . "'>"  . $parent->title . "</span></a></td>";
        $text .= "<td class = 'parenttag em06'>" . $tag . "</td><td class = 'answercomments em08'></td></tr>";
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
        $parent = $this->misc->getItemDetails($item['isanswer']);
        if ($parent->parentid == null) {
            $color = "delared";
        } else {
            $color = "delablue";
        }
        $decodeMarkdown = json_decode($parent->comment);
        $tag = $this->getTags($decodeMarkdown->frontmatter->tags);

        $text = "<td class = 'title'><a href='" . $viewone . "/" . $item['id'] . "'><span class='delablue'>" . $item['comm']->frontmatter->title . "</span></a></td class = 'tag em06'><td></td>";
        $text .= "<td class = 'parent em08'><a href='" . $viewone . "/" . $parent->id . "'><span class='" . $color . "'>"  . $parent->title . "</span></a></td>";
        $text .= "<td class = 'parenttag em06'>" . $tag . "</td><td class = 'answercomments em08'></td></tr>";

        return $text;
    }


 


    /**
     * @param object $comments
     *
     * @return array - commentitems for the userpage
     */    
    public function populateArray($comments)
    {
        $array = [];
        foreach ($comments as $key => $val) {
            $obj = [];
            $obj['comm'] = json_decode($val->comment);
            $obj['isanswer'] = $this->help->getIsAnswer($val->parentid, $val->iscomment);
            $obj['iscomment'] = $this->help->getIsComment($val->parentid, $val->iscomment);
            $obj['id'] = $val->id;
            $parentid = "parentid = ? AND iscomment < 1 OR parentid = ? AND iscomment IS NULL";
            $obj['hasanswer'] = $this->misc->findAllWhere($parentid, [$obj['id'], $obj['id']]);
            $obj['hascomments'] = $this->misc->findAllWhere("parentid = ? AND iscomment > 0", $obj['id']);
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
        $ques = $this->help->getQuesPoints($this->chosenid) ? ' [' . $this->help->getQuesPoints($this->chosenid) . '] ': "";
        $ans = $this->help->getAnsPoints($this->chosenid) ? ' [' . $this->help->getAnsPoints($this->chosenid) . '] ': "";
        $com = $this->help->getComPoints($this->chosenid) ? ' [' . $this->help->getComPoints($this->chosenid) . '] ': "";
        $text = '<table class = "member tagpage"><tr>';
        $text .= '<th class = "title"><span class = "delared">Fråga' . $ques . '</span> | <span class = "delablue">Svar' . $ans . '</span> | <span class = "delagreen">Kommentar' . $com . '</span></th><th class = "tag em06">Taggar</th><th class = "parent em08">Till <span class = "delared">Fråga</span> | <span class = "delablue">Svar</span></th><th class = "parenttag em06">Taggar</th><th class = "answercomments em08">Har <span class = "delablue">Svar</span> | <span class = "delagreen">Kommentarer</span></th></tr>';
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
        $viewone = $this->misc->setUrlCreator("comm/view-one");
        $virgin = true;
        $array = [];
        $grav = $this->misc->getGravatar($this->person->email, 50);
        $reputation = 0;

        if ($this->comments) {
            $array = $this->populateArray($this->comments);
            $virgin = false;
        }

        if ($virgin == true) {
            $startinfo = $this->person->acronym . " har inte gjort några inlägg ännu.";
        } else {
            $startinfo = $this->help->getPointsHTML($reputation, $this->chosenid);
        }

        $text = $this->getTableHead();

        foreach ($array as $value) {
            $text .= '<tr>';

            if ($value['isanswer'] == null && $value['iscomment'] == null) {
                $text .= $this->getQuestionHTML($value, $viewone);

                $points = $value['points'] + 0.5;
                $questionValue = $points * 3;
                $reputation += $questionValue;
            } elseif ($value['iscomment']) {
                $text .= $this->getCommentHTML($value, $viewone);

                $points = $value['points'] + 0.5;
                $commentValue = $points * 2;
                $reputation += $commentValue;
            } elseif ($value['isanswer']) {
                $text .= $this->getAnswersHTML($value, $viewone);

                $points = $value['points'] + 0.5;
                $bonus = $this->misc->findAllWhere("accept = ?", $value['id']);
                if ($bonus) {
                    $answerValue = $points * 8;
                } else {
                    $answerValue = $points * 4;
                }
                $reputation += $answerValue;
            }
        }
        $text .= "</table><br />";

        $html .= $grav;
        $html .= '<h1>' . $this->person->acronym . '</h1>';
        $html .= $startinfo;
        $html .= $text;
        $html .= '</div>';

        return $html;
    }
}
