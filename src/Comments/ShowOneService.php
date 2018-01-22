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
        $this->getComments($sort, $id);

        $session = $this->di->get("session");
        $this->sess = $session->get("user");
        $this->isadmin = $this->sess['isadmin'] == 1 ? true : false;

        $this->commtext = "";
        $this->linkup = "";
        $this->linkdown = "";
        $this->linkend = "";
    }


    /**
    * Get the comments int the order we want
    */
    public function getComments($sort, $id)
    {
        $orderby = $sort == 1 ? "`points` DESC" : "`created` DESC";//ORDER BY fixed by ActiveRecord
        $params = [$id];
        $this->comments = $this->getParentOrderDetails($orderby, $params);
    }


    /**
     * Get details on item to load form with.
     *
     * @param integer $id get details on item with id.
     *
     * @return Comm
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
     * @param string $orderby - column and DESC or ASC
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
    public function getDecode(Comm $item, $iscomment = null)
    {
        $comt = json_decode($item->comment);
        $til = $comt->frontmatter->title ? $comt->frontmatter->title : $item->title;
        $comt = $comt->text;

        $text = $iscomment ? '<h5>' . $til . '</h5>' . '<span class = "pcomment">' . $comt . '</span>' : '<h4>' . $til . '</h4>' . $comt;
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
        $open .= '<span class="canaccept"><span class="canaccepttext">Klicka för att acceptera</span>' . $faaccept . '</span></a><hr />';
        $closed = ' | <span class="canaccept"><span class="canaccepttext">Accepterat svar</span>' . $faaccept . '</span><hr />';

        return $toaccept > 0 ? $open : ($isaccepted == $id ? $closed : "");
    }

    /**
    * sets variables for when not a member
    */
    public function noMember()
    {
        $this->linkup = '<span class="hasvoted">';
        $this->linkdown = '<span class="hasvoted">';
        $this->linkend = '</span>';
    }


    /**
    * sets variables for when member has voted
    */
    public function votedMember()
    {
        $this->linkup = '<span class="hasvoted"><span class="hasvotedtext">Man kan bara rösta en gång</span>';
        $this->linkdown = '<span class="hasvoted"><span class="hasvotedtext">Man kan bara rösta en gång</span>';
        $this->linkend = '</span>';        
    }


    /**
    * sets variables for when commentowner
    */
    public function commentOwner()
    {
        $this->linkup = '<span class="hasvoted"><span class="hasvotedtext">Det går inte rösta på sig själv</span>';
        $this->linkdown = '<span class="hasvoted">';
        $this->linkend = '</span>';
    }


    /**
    * sets variables for when member can vote
    */
    public function canVote($voteup, $votedown, $id)
    {
        $this->linkup = '<a href="' . $voteup . '/' . $id . '"><span class="canvote"><span class="canvotetext">+1</span>';
        $this->linkdown = '<a href="' . $votedown . '/' . $id . '"><span class="canvote"><span class="canvotetext">-1</span>';
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

        $arr_decoded = json_decode($value->hasvoted);
        $faup = '<i class="fa fa-thumbs-up" aria-hidden="true"></i>';
        $fadown = '<i class="fa fa-thumbs-down" aria-hidden="true"></i>';

        if ($this->sess == null) {
            $this->noMember();
        } elseif ($arr_decoded && in_array($this->sess['id'], $arr_decoded)) {
            $this->votedMember();
        } elseif ($this->sess['id'] == $value->userid) {
            $this->commentOwner();
        } else {
            $this->canVote($voteup, $votedown, $value->id);
        }
        $points = $value->points ? ' | <span class = "smaller">[' . $value->points . ']</span>' : "";

        return ' | ' . $this->linkup . $faup . $this->linkend . ' | ' . $this->linkdown . $fadown . $this->linkend . $points;
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
    public function getLoginurl()
    {
        $loginurl = $this->setUrlCreator("user/login");
        $create = $this->setUrlCreator("comm/create");
        $commentpath = $this->setUrlCreator("comm/comment");

        $notloggedin = '<a href="' . $loginurl . '">Logga in om du vill svara</a></p>';
        $hasloggedin = '<a href="' . $create . '/' . $this->comment->id . '">Svara</a>' . ' | <a href="' . $commentpath . '/' . $this->comment->id . '">Kommentera</a>';

        return $this->sess && $this->sess['id'] ? $hasloggedin : $notloggedin;
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
    public function getEdit($userid, $id, $htmlcomment)
    {
        $update = $this->setUrlCreator("comm/update");
        $admintext = '<p><a href="' . $update . '/' . $id . '">Redigera</a> | ' . $htmlcomment;
        $normal = "<p>" . $htmlcomment . "</p>";
        return $this->isadmin || $userid == $this->sess['id'] ? $admintext : $normal;
    }


    /**
     * If session contains correct id, returns string with edit-links
     *
     * @return string htmlcode
     */
    public function getDelete($userid, $del, $id)
    {
        return $this->isadmin || $userid == $this->sess['id'] ? ' | <a href="' .  $del . '/' . $id . '">Ta bort inlägget</a></p>' : "";
    }


    /**
     * @param object - $value - commentitem
     *
     * @return string htmlcode
     */
    public function getCommCommVal($value)
    {
        $text = '<div class = "move20"><hr />' . $this->getValHtml($value, 1);
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
        foreach ($commcomments as $key => $value) {
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
    public function getValHtml(Comm $item, $iscomment = null)
    {
        $userController = $this->di->get("userController");
        $curruser = $userController->getOne($item->userid);

        $email = $curruser['email'];
        $gravatar = $this->getGravatar($email);
        $updated = isset($item->updated) ? '| Ändrad: ' . $item->updated : "";
        
        $text = $this->getDecode($item, $iscomment);
        $text .= '<p><span class="smaller">' . $email . '</span> ' . $gravatar . '<br />';
        $text .= '<span class="smaller">Skrevs: ' . $item->created . ' ' . $updated . '</span>';
        
        return $text;
    }


    /**
     * Returns htmltext for each item
     *
     * @return string htmlcode
     */
    public function getHTMLItem($value, $commentpath)
    {
        $params = [$value->id];
        $toaccept = $value->iscomment == 0 ? $this->getToAccept() : "";
        $text = $value->iscomment == 0 ? $this->getValHtml($value) . '<br /><a href="' . $commentpath . '/' . $value->id . '">Kommentera</a>' . $this->getVoteHtml($value) : "";
        $text .= $value->iscomment == 0 ? $this->getAcceptIcon($toaccept, $this->comment->accept, $value->id) : "";
        $commcomments = $value->iscomment == 0 ? $this->getParentDetails($params) : null;
        $text .= ($commcomments) ? $this->getCommComments($commcomments)  : "";
        $this->commtext .= $value->iscomment == 1 ? $this->getValHtml($value, 1) . $this->getVoteHtml($value) . '<hr />' : "";
        return $text;
    }


    /**
     * @param string $commentpath
     * @return string htmlcode for comments
     */
    public function getCommentItem($commentpath)
    {
        $text = "";
        foreach ($this->comments as $value) {
            $text .= $this->getHTMLItem($value, $commentpath);
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
        $del = $this->setUrlCreator("comm/delete");
        $commentpath = $this->setUrlCreator("comm/comment");
        $commpage = $this->setUrlCreator("comm");
        $pointsort = $this->setUrlCreator("comm/commentpoints");
        $datesort = $this->setUrlCreator("comm/view-one");
        
        $htmlcomment = $this->getLoginurl();
        $edit = $this->getEdit($this->comment->userid, $this->comment->id, $htmlcomment);
        $delete = $this->getDelete($this->comment->userid, $del, $this->comment->id);

        $text = '<h2>Svar <span class = "smallpoints"><a href="' . $pointsort . '/' . $this->comment->id . '">rankordning</a> | <a href="' . $datesort . '/' . $this->comment->id . '"> datumordning</a></span></h2>';
        
        $this->commtext = $this->comments ? "<h3>Kommentarer</h3>" : "";
        $text .= $this->comments ? $this->getCommentItem($commentpath) : "";

        $html = '<div class="col-sm-12 col-xs-12"><div class="col-lg-6 col-sm-7 col-xs-12">';
        $html .= $this->getValHtml($this->comment);
        $html .= '<br />' . $edit . $delete . '<hr />' . $this->commtext;
        $html .=  '<p><a href="' . $commpage . '">Till Frågor</a></p>';
        $html .=  '</div><div class="col-sm-5 col-xs-12">' . $text . '</div></div>';
        return $html;
    }
}
