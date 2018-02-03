<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\Misc;
use \Guni\Comments\FromDb;

use \Guni\Comments\VoteService;
use \Guni\User\UserHelp;

/**
 * Form to update an item.
 */
class ShowOneService
{
    /**
    * @var array $comment, the chosen comment.
    */
    protected $comment;
    protected $comments;
    protected $sess;
    protected $isadmin;
    protected $commtext;
    protected $linkup;
    protected $linkend;
    protected $linkdown;
    protected $di;
    protected $isquestion;
    protected $isanswer;
    protected $iscomment;
    protected $misc;
    protected $userhelp;
    protected $fromdb;


    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to show
     */
    public function __construct(DIInterface $di, $id, $sort = null)
    {
        $this->di = $di;
        $this->fromdb = new FromDb($di);
        $this->misc = new Misc($di);
        $this->userhelp = new UserHelp($di);
        $this->comment = $this->fromdb->getItemDetails($id);
        $this->comments = $this->fromdb->getAnswers($sort, $id, $this->comments);

        $session = $this->di->get("session");
        $this->sess = $session->get("user");
        $this->isadmin = $this->sess['isadmin'] == 1 ? true : false;

        $this->commtext = "";
        $this->linkup = "";
        $this->linkdown = "";
        $this->linkend = "";
        $this->isquestion = 0;
        $this->isanswer = 0;
        $this->iscomment = 0;
    }



    /**
     * Returns json_decoded title and text
     * @param object $item
     * @return string htmlcode
     */
    public function getDecode(Comm $item, $iscomment = null, $lead = null)
    {
        $comt = json_decode($item->comment);
        $til = $comt->frontmatter->title ? $comt->frontmatter->title : $item->title;
        $comt = $comt->text;

        $text = $iscomment ? '<h5>' . $til . '</h5>' . '<span class = "em06">' . $comt . '</span>' : ($lead ?  '<h1>' . $til . '</h1>' . $comt : '<h4>' . $til . '</h4>' . $comt);
        return $text;
    }


    /**
     * If loggedin allowed to edit
     *
     * @param integer $toaccept - if owner of question he can accept
     * @param integer $isaccepted - can only accept one answer
     * @param integer $id - the question
     *
     * @return string htmlcode
     */
    public function getAcceptIcon($toaccept, $isaccepted, $id)
    {
        $faaccept = '<i class="fa fa-smile-o" aria-hidden="true"></i>';

        $open = ' | <a href="' . $this->misc->setUrlCreator("comm/accept") . '/' . $id . '">';
        $open .= '<span class="canaccept em08"><span class="canaccepttext">Klicka för att acceptera</span>' . $faaccept . '</span></a><hr />';
        $closed = ' | <span class="canaccept em08"><span class="canaccepttext">Accepterat svar</span>' . $faaccept . '</span><hr />';

        return $toaccept > 0 ? $open : ($isaccepted == $id ? $closed : "");
    }

    /**
    * sets variables for when not a member
    */
    public function noMember()
    {
        $this->linkup = '<span class="hasvoted em08">';
        $this->linkdown = '<span class="hasvoted em08">';
        $this->linkend = '</span>';
    }


    /**
    * sets variables for when member has voted
    */
    public function votedMember()
    {
        $this->linkup = '<span class="hasvoted em08"><span class="hasvotedtext">Man kan bara rösta en gång</span>';
        $this->linkdown = '<span class="hasvoted em08"><span class="hasvotedtext">Man kan bara rösta en gång</span>';
        $this->linkend = '</span>';        
    }


    /**
    * sets variables for when commentowner
    */
    public function commentOwner()
    {
        $this->linkup = '<span class="hasvoted em08"><span class="hasvotedtext">Det går inte rösta på sig själv</span>';
        $this->linkdown = '<span class="hasvoted em08">';
        $this->linkend = '</span>';
    }


    /**
    * sets variables for when member can vote
    */
    public function canVote($id)
    {
        $this->linkup = '<a href="' . $this->misc->setUrlCreator("comm/voteup") . '/' . $id . '"><span class="canvote em08"><span class="canvotetext">+1</span>';
        $this->linkdown = '<a href="' . $this->misc->setUrlCreator("comm/votedown") . '/' . $id . '"><span class="canvote em08"><span class="canvotetext">-1</span>';
        $this->linkend = '</span></a>';
    }



    /**
    * @param obj $value - commentobject
    *
    * @return string html-text for voting
    */
    public function voteChoices($value)
    {
        $arrDecoded = json_decode($value->hasvoted);
        $faup = $this->sess == null ? "" : ' | <i class="fa fa-thumbs-up" aria-hidden="true"></i>';
        $fadown = $this->sess == null ? "" : ' | <i class="fa fa-thumbs-down" aria-hidden="true"></i>';

        if ($this->sess == null) {
            $this->noMember();
        } elseif ($arrDecoded && in_array($this->sess['id'], $arrDecoded)) {
            $this->votedMember();
        } elseif ($this->sess['id'] == $value->userid) {
            $this->commentOwner();
        } else {
            $this->canVote($value->id);
        }
        $points = $value->points ? ' | <span class = "pcomm smaller em06"><span class="pcommtext">Poäng</span>[' . $value->points . ']</span>' : "";

        return $this->linkup . $faup . $this->linkend . $this->linkdown . $fadown . $this->linkend . $points;
    }



    /**
     *
     * @param obj $value
     *
     * @return string htmlcode - icons to vote up or down
     */
    public function getVoteHtml($value)
    {
        return ((string)$value->userid !== (string)$this->sess['id']) ? $this->voteChoices($value) : "";
    }


    /**
     * If session contains correct id, returns string with edit-links
     *
     * @return string htmlcode
     */
    public function getLoginurl($commentid)
    {
        $notloggedin = $this->isquestion ? '<a href="' . $this->misc->setUrlCreator("user/login") . '">Logga in om du vill svara</a></p>' : "";

        $answer = $this->isquestion ? ' | ' . $this->misc->getAnswerLink($commentid) : "";
        $comment = $this->iscomment ? "" : ' | ' . $this->misc->getCommentLink($commentid);
        $edit = $this->getEditLink($commentid);
        $delete = ' | ' . $this->getDeleteLink($commentid) . " ";

        $hasloggedin = $edit . $answer . $comment . $delete;


        return $this->isadmin || $this->sess && $this->sess['id'] ? $hasloggedin : $notloggedin;
    }


    /**
     * If loggedin allowed to edit
     *
     * @param string $userid
     * @param string $id
     * @param string $htmlcomment, link
     *
     * @return string htmlcode
     */
    public function getEditLink($commentid)
    {
        return '<a href="' . $this->misc->setUrlCreator("comm/update") . '/' . $commentid . '">Redigera</a>';
    }


    /**
     * @param integer $commentid - current comment
     * @return string htmlcode path to delete
     */
    public function getDeleteLink($commentid)
    {
        $end = '/' . $commentid . '">Ta bort inlägget</a>';
        return '<a href="' . $this->misc->setUrlCreator("comm/delete") . $end;
    }


    /**
     * @param object - $value - commentitem
     *
     * @return string htmlcode
     */
    public function getCommCommVal($value)
    {
        $this->isquestion = 0;
        $this->isanswer = $value->iscomment == 1 ? 0 : 1;
        $this->iscomment = $value->iscomment;

        $text = '<div class = "move20"><hr />' . $this->getValHtml($value, 1, null) . '<br />' . $this->getLoginurl($value->id); 
        $text .= (string)$value->userid !== (string)$this->sess['id'] ? $this->getVoteHtml($value) : "";
        $text .= "</div>";
        return $text;
    }

    /**
     * @param array $commcomments - commentlist
     *
     * @return string htmlcode
     */
    public function getCommComments($commcomments)
    {
        $text = "";
        foreach ($commcomments as $value) {
            $text .= $this->getCommCommVal($value);
        }
        return $text;
    }

    /**
     * If a comment is accepted by questioner
     * @return integer - if comment is not accepted, else string
     */
    public function getToAccept()
    {
        $test = ((string)$this->comment->userid == (string)$this->sess['id'] && $this->comment->accept < 1) ? $this->comment->id : "";
        return $test;
    }

    /**
     * Returns html for each item
     *
     * @param object $item
     * @param string $can
     *
     * @return string htmlcod
     */
    public function getValHtml(Comm $item, $iscomment = null, $lead = null)
    {
        $curruser = $this->userhelp->getOne($item->userid);

        $email = $curruser['email'];
        $gravatar = $this->misc->getGravatar($email);
        $acronym = $curruser['acronym'];
        $updated = isset($item->updated) ? '| Ändrad: ' . $item->updated : "";
        
        $text = $this->getDecode($item, $iscomment, $lead);
        $text .= '<p><span class="smaller em06">' . $acronym . '</span> ' . $gravatar . '<br />';
        $text .= '<span class="smaller em06">Skrevs: ' . $item->created . ' ' . $updated . '</span>';
        
        return $text;
    }


    /**
     * Returns htmltext for each answer item
     * @param object $value - the questionitem
     * @return string htmlcode
     */
    public function getStarted($value)
    {
        return $value->iscomment == 0 ? $this->getValHtml($value) . '<br />' . $this->getLoginurl($value->id) . $this->getVoteHtml($value) : "";
    }



    /**
     * Returns htmltext for each answer item
     * @param object $value - the questionitem
     * @return string htmlcode
     */
    public function getNextBit($value)
    {
        $toaccept = $value->iscomment == 0 ? $this->getToAccept() : "";
        return $value->iscomment == 0 ? $this->getAcceptIcon($toaccept, $this->comment->accept, $value->id) : "";
    }



    /**
     * Returns htmltext for each answer item
     * @param object $value - the questionitem
     */
    public function makeCommentText($value)
    {
        $this->commtext .= $value->iscomment == 1 ? $this->getValHtml($value, 1, null) . '<br />' . $this->getLoginurl($value->id) . $this->getVoteHtml($value) . '<hr />' : "";
    }



    /**
     * Returns htmltext for each answer item
     * @param object $value - the questionitem
     * @return string htmlcode
     */
    public function getHTMLItem($value)
    {
        $this->isquestion = 0;
        $this->iscomment = $value->iscomment == 1 ? 1 : 0;
        $this->isanswer = $value->iscomment == 1 ? 0 : 1;
        $params = [$value->id];

        $text = $this->getStarted($value);
        $text .= $this->getNextBit($value);

        $commcomments = $value->iscomment == 0 ? $this->fromdb->findAllWhere("parentid = ?", $params) : null;
        $text .= ($commcomments) ? $this->getCommComments($commcomments)  : "";

        $this->makeCommentText($value);

        return $text;
    }


    /**
     * @return string $text htmlcode for comments
     */
    public function getCommentItem()
    {
        $text = "";
        foreach ($this->comments as $value) {
            $text .= $this->getHTMLItem($value);
        }
        return $text;
    }



    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $this->isquestion = 1;
        $this->isanswer = 0;
        $this->iscomment = 0;
        
        $mainCommentLinks = $this->getLoginurl($this->comment->id);

        $text = '<h2>Svar <span class = "em06"><a href="' . $this->misc->setUrlCreator("comm/commentpoints") . '/' . $this->comment->id . '">rankordning</a> | <a href="' . $this->misc->setUrlCreator("comm/view-one") . '/' . $this->comment->id . '"> datumordning</a></span></h2>';
        
        $this->commtext = $this->comments ? "<h3>Kommentarer</h3>" : "";
        $text .= $this->comments ? $this->getCommentItem() : "";

        $html = '<div class="col-sm-12 col-xs-12"><div class="col-lg-6 col-sm-7 col-xs-12">';
        $html .= $this->getValHtml($this->comment, 0, 1);
        $html .= '<br />' . $mainCommentLinks . '<hr />' . $this->commtext;
        $html .=  '<p><a href="' . $this->misc->setUrlCreator("comm") . '">Till Frågor</a></p>';
        $html .=  '</div><div class="col-sm-5 col-xs-12">' . $text . '</div></div>';
        return $html;
    }
}
