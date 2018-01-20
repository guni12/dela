<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;

/**
 * 
 */
class ShowTagsService
{
    /**
    * @var array $tags
    */
    protected $tagset;
    protected $name;
    protected $isadmin;
    protected $sess;


    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di, $id)
    {
        $this->di = $di;
        $this->name = $id;
        $search = "%" . $id . "%";
        $this->tagset = $this->getTags($search);

        $session = $this->di->get("session");
        $this->sess = $session->get("user");

        $this->isadmin = $this->sess['isadmin'] == 1 ? true : false;
    }


    /**
     * Get details on item to load form with.
     *
     * @param integer $id get details on item with id.
     *
     * @return Comm
     */
    public function getTags($search)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        //$sql = 'SELECT * FROM `comm` WHERE comment LIKE "%elcar%"';

        return $comm->findAllWhere('comment LIKE ?', $search);
    }


    /**
     * Get details on item to load form with.
     *
     * @param string $where
     * @param array $params get details on item with id parentid.
     *
     * @return Comm
     */
    public function getChildrenDetails($childid)
    {
        $searchChosenid = "parentid = ?";
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findAllWhere($searchChosenid, $childid);
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
    public function getDecode(Comm $item, $lead = null)
    {
        $comt = json_decode($item->comment);
        if ($comt->frontmatter->title) {
            $til = $comt->frontmatter->title;
        } else {
            $til = $item->title;
        }
        $comt = $comt->text;
        if ($lead) {
            return '<h3>' . $til . '</h3><p>' . $comt . '</p>';
        }
        return '<h4>' . $til . '</h4><p>' . $comt . '</p>';
    }


    /**
     * If param met, returns string with edit-links
     * @param object $item
     * @param string $del
     * @return string htmlcode
     */
    public function getCanEdit(Comm $item, $del)
    {
        $canedit = "";
        $update = $this->setUrlCreator("comm/update");
        if ($item->userid == $this->sess['id'] || $this->isadmin) {
            $canedit = '<br /><a href="' . $update . '/' . $item->id . '">Redigera</a>';
            $canedit .= ' | <a href="' . $del . '/' . $item->id . '">Ta bort inlägget</a></p>';
        } else {
            $canedit .= '</p>';
        }
        return $canedit;
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

        $htmlcomment = '<a href="' . $loginurl . '">Logga in om du vill svara</a></p>';

        if ($this->sess && $this->sess['id']) {
            $htmlcomment = '<a href="' . $create . '/' . $this->comment->id . '">Svara</a>';
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



    public function getHeadline($name) {
        switch ($name) {
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
                $headline = "Värme";
        }
    }


    /**
     * Returns html for each item
     *
     * @param object $item
     * @param string $can
     *
     * @return string htmlcod
     */
    public function getValHtml(Comm $item, $can)
    {
        $gravatar = $this->getGravatar($item->email);
        $updated = isset($item->updated) ? '| Uppdaterades: ' . $item->updated : "";
        
        $text = $this->getDecode($item);
        $text .= '<p><span class="smaller">' . $item->email . '</span> ' . $gravatar . '<br />';
        $text .= 'Skrevs: ' . $item->created . ' ' . $updated;
        $text .= $can;
        $text .= '<hr />';
        return $text;
    }


    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $view = $this->setUrlCreator("tags/view-one");
        $viewcomm = $this->setUrlCreator("comm/view-one");
        $base = $this->setUrlCreator("comm/tags/");

        $headline = $this->getHeadline($this->name);

        $html = "<h1>" . $headline . "</h1>";
        $html .= '<table class = "member tagpage"><tbody><tr>';
        $html .= '<th class = "itis"></th>';
        $html .= '<th class = "author"></th>';
        $html .= '<th class = "tagheadline">Fråga</th>';
        $html .= '<th class = "taganswers">Svar</th>';
        $html .= '<th class = "tagcomments">Kommentarer</th>';
        $html .= '</tr>';

        $question = "Fråga: ";
        $excla = "Svar: ";
        $parent = "";

        $userController = $this->di->get("userController");

        foreach ($this->tagset as $key => $val) {
            $answers = "";
            $comments = "";
            //var_dump($val);
            $children = $this->getChildrenDetails($val->id);
            foreach ($children as $count => $item) {
                //var_dump($item);
                //var_dump($item->id, $item->title, $item->iscomment);
                if ($item->iscomment == 1) {
                    $comments .= '<a href = "' . $viewcomm . '/' . $item->id . '">' . $item->title . '</a>, ';
                } else {
                    $answers .= '<a href = "' . $viewcomm . '/' . $item->id . '">' . $item->title . '</a>, ';
                }
            }
            $answers = rtrim($answers, ', ');
            $comments = rtrim($comments, ', ');
            $childrentext = "<td class = 'taganswers'>" . $answers . "</td><td class = 'comments'>" . $comments . "</td>";
            $html .= "<tr>";
            $user = $userController->getOne($val->userid);
            $grav = $this->getGravatar($user['email']);
            $acronym = $user['acronym'];
            $html .= '<td class = "itis"><span class="smaller">' . $acronym . '</span></td>';
            $html .= '<td>' . $grav . '</td>';
            $html .= "<td><a href = '" . $viewcomm . "/" . $val->id . "'>" . $val->title . "</td>";
            $html .= $childrentext;
            $html .= '</tr>';
        }

        $html .= "</tbody></table>";

        return $html;
    }
}
