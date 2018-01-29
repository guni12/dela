<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;

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


    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to show
     */
    public function __construct(DIInterface $di, $id, $sort = null)
    {
        $this->di = $di;
        $this->comment = $this->getItemDetails($id);
        $this->getAnswers($sort, $id);

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
    * sql command ORDER BY fixed by ActiveRecord
    *
    * @param integer $sort - if answers should be sorted by points
    * @param integer $id - the question for the answers anwers
    */
    public function getAnswers($sort, $id)
    {
        $orderby = $sort == 1 ? "`points` DESC" : "`created` DESC";
        $params = [$id];
        $this->comments = $this->getParentOrderDetails($orderby, $params);
    }


    /**
     * Get details on item to load form with.
     *
     * @param integer $id get details on item with id.
     *
     * @return object $comm - actual comment
     */
    public function getItemDetails($id)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $comm->find("id", $id);
        return $comm;
    }


        /**
     * Get details on item to load form with.
     *
     * @param array $params get details on item with id parentid.
     *
     * @return Comm
     */
    public function getParentDetails($params)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findAllWhere("parentid = ?", $params);
    }

        /**
     * Get details on item to load form with.
     *
     * @param string $orderby - sql command for column and DESC or ASC
     * @param array $params get details on item with id parentid.
     *
     * @return Comm
     */
    public function getParentOrderDetails($orderby, $params)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findOrderBy("parentid = ?", $orderby, $params);
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
     * @return string htmlcode
     */
    public function getGravatar($item)
    {
        $comm = new Comm();
        $gravatar = $comm->getGravatar($item);
        return '<img src="' . $gravatar . '" alt=""/>';
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
        $acceptlink = $this->setUrlCreator("comm/accept");
        $faaccept = '<i class="fa fa-smile-o" aria-hidden="true"></i>';

        $open = ' | <a href="' . $acceptlink . '/' . $id . '">';
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
    public function canVote($voteup, $votedown, $id)
    {
        $this->linkup = '<a href="' . $voteup . '/' . $id . '"><span class="canvote em08"><span class="canvotetext">+1</span>';
        $this->linkdown = '<a href="' . $votedown . '/' . $id . '"><span class="canvote em08"><span class="canvotetext">-1</span>';
        $this->linkend = '</span></a>';
    }



    /**
    * @param obj $value - commentobject
    *
    * @return string html-text for voting
    */
    public function voteChoices($value)
    {
        $voteup = $this->setUrlCreator("comm/voteup");
        $votedown = $this->setUrlCreator("comm/votedown");

        $arrDecoded = json_decode($value->hasvoted);
        $faup = ' | <i class="fa fa-thumbs-up" aria-hidden="true"></i>';
        $fadown = ' | <i class="fa fa-thumbs-down" aria-hidden="true"></i>';

        if ($this->sess == null) {
            $this->noMember();
            $faup = "";
            $fadown = "";
        } elseif ($arrDecoded && in_array($this->sess['id'], $arrDecoded)) {
            $this->votedMember();
        } elseif ($this->sess['id'] == $value->userid) {
            $this->commentOwner();
        } else {
            $this->canVote($voteup, $votedown, $value->id);
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
     * @param integer $commentid - question to answer
     *
     * @return string htmlcode for answering a question
     */
    public function getAnswerLink($commentid)
    {
        $create = $this->setUrlCreator("comm/create");
        return '<a href="' . $create . '/' . $commentid . '">Svara</a>';
    }



    /**
     * @param integer $commentid - question or answer to comment
     *
     * @return string htmlcode for commenting
     */
    public function getCommentLink($commentid)
    {
        $commentpath = $this->setUrlCreator("comm/comment");
        return '<a href="' . $commentpath . '/' . $commentid . '">Kommentera</a>';
    }



    /**
    * @param $edit, $answer, $comment, $delete - paths/links
    *
    */
    public function makeLoginText($edit, $answer, $comment, $delete)
    {
        $line = $edit && $answer ? ' | ' : "";
        $line2 = $answer && $comment || $edit && $comment ? ' | ' : "";
        $line3 = $comment && $delete || $edit && $delete || $answer && $delete? ' | ' : "";
        return $edit . $line . $answer . $line2 . $comment . $line3 . $delete;
    }




    /**
     * If session contains correct id, returns string with edit-links
     *
     * @return string htmlcode
     */
    public function getLoginurl($commentid)
    {
        $loginurl = $this->setUrlCreator("user/login");
        $notloggedin = $this->isquestion ? '<a href="' . $loginurl . '">Logga in om du vill svara</a></p>' : "";

        $answer = $this->isquestion ? $this->getAnswerLink($commentid) : "";
        $comment = $this->iscomment ? "" : $this->getCommentLink($commentid);
        $edit = $this->getEditLink($commentid);
        $delete = $this->getDeleteLink($commentid);

        $hasloggedin = $this->makeLoginText($edit, $answer, $comment, $delete);

        

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
        $editpath = $this->setUrlCreator("comm/update");
        return '<a href="' . $editpath . '/' . $commentid . '">Redigera</a>';
    }




    /**
     * @param integer $commentid - current comment
     * @return string htmlcode path to delete
     */
    public function getDeleteLink($commentid)
    {
        $del = $this->isadmin ? "comm/admindelete" : "comm/delete";
        $deletepath = $this->setUrlCreator($del);
        $end = $this->isadmin ? '">Ta bort inlägget</a>' : '/' . $commentid . '">Ta bort inlägget</a>';
        return '<a href="' . $deletepath . $end;
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
     * @param object $commcomments - commentlist
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
        $userController = $this->di->get("userController");
        $curruser = $userController->getOne($item->userid);

        $email = $curruser['email'];
        $gravatar = $this->getGravatar($email);
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
    public function getHTMLItem($value)
    {
        $this->isquestion = 0;
        $this->iscomment = $value->iscomment == 1 ? 1 : 0;
        $this->isanswer = $value->iscomment == 1 ? 0 : 1;
        $params = [$value->id];
        $toaccept = $value->iscomment == 0 ? $this->getToAccept() : "";
        $text = $value->iscomment == 0 ? $this->getValHtml($value) . '<br />' . $this->getLoginurl($value->id) . $this->getVoteHtml($value) : "";
        $text .= $value->iscomment == 0 ? $this->getAcceptIcon($toaccept, $this->comment->accept, $value->id) : "";
        $commcomments = $value->iscomment == 0 ? $this->getParentDetails($params) : null;
        $text .= ($commcomments) ? $this->getCommComments($commcomments)  : "";
        $this->commtext .= $value->iscomment == 1 ? $this->getValHtml($value, 1, null) . '<br />' . $this->getLoginurl($value->id) . $this->getVoteHtml($value) . '<hr />' : "";
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
        $commpage = $this->setUrlCreator("comm");
        $pointsort = $this->setUrlCreator("comm/commentpoints");
        $datesort = $this->setUrlCreator("comm/view-one");

        $this->isquestion = 1;
        $this->isanswer = 0;
        $this->iscomment = 0;
        
        $mainCommentLinks = $this->getLoginurl($this->comment->id);

        $text = '<h2>Svar <span class = "em06"><a href="' . $pointsort . '/' . $this->comment->id . '">rankordning</a> | <a href="' . $datesort . '/' . $this->comment->id . '"> datumordning</a></span></h2>';
        
        $this->commtext = $this->comments ? "<h3>Kommentarer</h3>" : "";
        $text .= $this->comments ? $this->getCommentItem() : "";

        $html = '<div class="col-sm-12 col-xs-12"><div class="col-lg-6 col-sm-7 col-xs-12">';
        $html .= $this->getValHtml($this->comment, 0, 1);
        $html .= '<br />' . $mainCommentLinks . '<hr />' . $this->commtext;
        $html .=  '<p><a href="' . $commpage . '">Till Frågor</a></p>';
        $html .=  '</div><div class="col-sm-5 col-xs-12">' . $text . '</div></div>';
        return $html;
    }
}
