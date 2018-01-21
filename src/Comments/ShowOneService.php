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

        if ($sort == 1) {
            $where = "parentid = ?";
            $orderby = "`points` DESC"; //ORDER BY by AR
            $params = [$id];
            $this->comments = $this->getParentOrderDetails($where, $orderby, $params);
        } else {
            $where = "parentid = ?";
            $orderby = "`created` DESC"; //ORDER BY by AR
            $params = [$id];
            $this->comments = $this->getParentOrderDetails($where, $orderby, $params);
        }

        $session = $this->di->get("session");
        $this->sess = $session->get("user");

        $this->isadmin = $this->sess['isadmin'] == 1 ? true : false;
        $this->commtext = "";
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
     * Get details on item to load form with.
     *
     * @param string $where
     * @param string $orderby - column and DESC or ASS
     * @param array $params get details on item with id parentid.
     *
     * @return Comm
     */
    public function getParentOrderDetails($where, $orderby, $params)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findOrderBy($where, $orderby, $params);
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
     * If lead text, headline is larger font
     * @param object $item
     * @return string htmlcode
     */
    public function getDecode(Comm $item, $lead = null, $iscomment = null)
    {
        $comt = json_decode($item->comment);
        if ($comt->frontmatter->title) {
            $til = $comt->frontmatter->title;
        } else {
            $til = $item->title;
        }
        $comt = $comt->text;

        if ($lead) {
            return '<h3>' . $til . '</h3>' . $comt;
        } elseif ($iscomment) {
            return '<h5>' . $til . '</h5>' . '<span class = "pcomment">' . $comt . '</span>';
        }
        return '<h4>' . $til . '</h4>' . $comt;
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

        $link = "";

        if ($toaccept > 0) {
            $link = ' | <a href="' . $acceptlink . '/' . $id . '">';
            $link .= '<span class="canaccept"><span class="canaccepttext">Klicka för att acceptera</span>';
            $link .= $faaccept;
            $link .= '</span></a>';            
        } elseif ($isaccepted == $id) {
            $link = ' | <span class="canaccept"><span class="canaccepttext">Accepterat svar</span>';
            $link .= $faaccept;
            $link .= '</span>'; 
        } else {
            $link = "";
        }

        return $link;
    }


    /**
     *
     * @param obj $value
     *
     * @return string htmlcode - icons to vote up or down
     */
    public function getVoteHtml($value)
    {
        $html = "";
        if ((string)$value->userid !== (string)$this->sess['id'])
        {
            $voteup = $this->setUrlCreator("comm/voteup");
            $votedown = $this->setUrlCreator("comm/votedown");

            $arr_decoded = json_decode($value->hasvoted);
            $faup = '<i class="fa fa-thumbs-up" aria-hidden="true"></i>';
            $fadown = '<i class="fa fa-thumbs-down" aria-hidden="true"></i>';

            if ($this->sess == null) {
                $linkup = '<span class="hasvoted">';
                $linkdown = '<span class="hasvoted">';
                $linkend = '</span>';
            } elseif ($arr_decoded && in_array($this->sess['id'], $arr_decoded)) {
                $linkup = '<span class="hasvoted"><span class="hasvotedtext">Man kan bara rösta en gång</span>';
                $linkdown = '<span class="hasvoted"><span class="hasvotedtext">Man kan bara rösta en gång</span>';
                $linkend = '</span>';
            } elseif ($this->sess['id'] == $value->userid) {
                $linkup = '<span class="hasvoted"><span class="hasvotedtext">Det går inte rösta på sig själv</span>';
                $linkdown = '<span class="hasvoted">';
                $linkend = '</span>';
            } else {
                $linkup = '<a href="' . $voteup . '/' . $value->id . '"><span class="canvote"><span class="canvotetext">+1</span>';
                $linkdown = '<a href="' . $votedown . '/' . $value->id . '"><span class="canvote"><span class="canvotetext">-1</span>';
                $linkend = '</span></a>';
            }
            $points = $value->points ? ' | <span class = "smaller">[' . $value->points . ']</span>' : "";

            $html = ' | ' . $linkup . $faup . $linkend;
            $html  .= ' | ' . $linkdown . $fadown . $linkend . $points;
        }
        return $html;
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


        $htmlcomment = '<a href="' . $loginurl . '">Logga in om du vill svara</a></p>';

        if ($this->sess && $this->sess['id']) {
            $htmlcomment = '<a href="' . $create . '/' . $this->comment->id . '">Svara</a>';
            $htmlcomment .= ' | <a href="' . $commentpath . '/' . $this->comment->id . '">Kommentera</a>';
            $htmlcomment .= $this->getVoteHtml($this->comment);
        }
        return $htmlcomment;
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
        if ($this->isadmin || $userid == $this->sess['id']) {
            $edit = '<p><a href="' . $update . '/' . $id . '">Redigera</a> | ';
            $edit .= $htmlcomment;
        } else {
            $edit = "<p>" . $htmlcomment . "</p>";
        }
        return $edit;
    }


    /**
     * If session contains correct id, returns string with edit-links
     *
     * @return string htmlcode
     */
    public function getDelete($userid, $del, $id)
    {
        $delete = "";
        if ($this->isadmin || $userid == $this->sess['id']) {
            $delete = ' | <a href="' .  $del . '/' . $id . '">Ta bort inlägget</a></p>';
        }
        return $delete;
    }


    /**
     * @param object - $value - commentitem
     *
     * @return string htmlcode
     */
    public function getCommComments($value)
    {
        $lead = null;
        $text = '<div class = "move20">';
        $text .= '<hr />';
        $text .= $this->getValHtml($value, $lead, 1);
        if ((string)$value->userid !== (string)$this->sess['id']) {
            $text .= $this->getVoteHtml($value);
        }
        $text .= "</div>";
    }

    /**
     * If a comment is accepted by questioner
     * @return integer - if comment is accepted, else string
     */
    public function getToAccept()
    {
        $toaccept = "";
        if ((string)$this->comment->userid == (string)$this->sess['id'] && $this->comment->accept < 1) {
            $toaccept = $this->comment->id;
        }
        return $toaccept;
    }

    /**
     * Returns html for each item
     *
     * @param object $item
     * @param string $can
     *
     * @return string htmlcod
     */
    public function getValHtml(Comm $item, $lead = null, $iscomment = null)
    {
        $userController = $this->di->get("userController");
        $curruser = $userController->getOne($item->userid);

        $email = $curruser['email'];
        $gravatar = $this->getGravatar($email);
        $updated = isset($item->updated) ? '| Ändrad: ' . $item->updated : "";
        
        $text = $this->getDecode($item, $lead, $iscomment);
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
        $where = "parentid = ?";
        $lead = null;
        $text = "";
        if ($value->iscomment == 0) {
            $toaccept = 0;

            $text .= $this->getValHtml($value);
            $text .= '<br /><a href="' . $commentpath . '/' . $value->id . '">Kommentera</a>';
            $text .= $this->getVoteHtml($value);
            $params = [$value->id];
            $commcomments = $this->getParentDetails($where, $params);
            $toaccept = $this->getToAccept();
            $text .= $this->getAcceptIcon($toaccept, $this->comment->accept, $value->id);
            if ($commcomments) {
                foreach ($commcomments as $key => $value) {
                    $text .= $this->getCommComments($value);
                }
            }
            $text .= '<hr />';
        } elseif ($value->iscomment == 1) {
            $this->commtext .= $this->getValHtml($value, $lead, 1);
            $this->commtext .= $this->getVoteHtml($value);
            $this->commtext .= '<hr />';
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
        $voteup = $this->setUrlCreator("comm/voteup");
        $votedown = $this->setUrlCreator("comm/votedown");
        
        $htmlcomment = $this->getLoginurl();
        $edit = $this->getEdit($this->comment->userid, $this->comment->id, $htmlcomment);
        $delete = $this->getDelete($this->comment->userid, $del, $this->comment->id);

        $text = '<h2>Svar <span class = "smallpoints"><a href="' . $pointsort . '/' . $this->comment->id . '">rankordning</a> | <a href="' . $datesort . '/' . $this->comment->id . '"> datumordning</a></span></h2>';

        if ($this->comments) {
            $this->commtext .= "<h3>Kommentarer</h3>";
            foreach ($this->comments as $value) {
                $text .= $this->getHTMLItem($value, $commentpath);
            }
        }

        $html = '<div class="col-sm-12 col-xs-12"><div class="col-lg-6 col-sm-7 col-xs-12">';
        $html .= $this->getValHtml($this->comment);
        $html .= '<br />' . $edit;
        $html .=  $delete;
        $html .= '<hr />';
        $html .= $this->commtext;
        $html .=  '<p><a href="' . $commpage . '">Till Frågor</a></p>';
        $html .=  '</div><div class="col-sm-5 col-xs-12">';
        $html .= $text;
        $html .= '</div></div>';
        return $html;
    }
}
