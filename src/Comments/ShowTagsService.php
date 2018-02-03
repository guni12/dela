<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\ShowOneService;
use \Guni\Comments\Misc;
use \Guni\User\UserHelp;

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
    protected $di;
    protected $misc;


    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di, $id)
    {
        $this->di = $di;
        $this->name = $id;

        $this->misc = new Misc($di);
        $search = "%" . $id . "%";
        $this->tagset = $this->misc->findAllWhere('comment LIKE ?', $search);

        $session = $this->di->get("session");
        $this->sess = $session->get("user");

        $this->isadmin = $this->sess['isadmin'] == 1 ? true : false;
    }


    public function getHeadline($name)
    {
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
            case "heat":
                return "Värme";
                break;
            default:
                return "Elbil";
        }
    }



    /**
    * @return string $html - htmltext for beginning of table
    */
    public function getTableStart()
    {
        $headline = $this->getHeadline($this->name);

        $html = "<h1>" . $headline . "</h1>";
        $html .= '<table class = "member tagpage"><tbody><tr>';
        $html .= '<th class = "itis"></th>';
        $html .= '<th class = "author"></th>';
        $html .= '<th class = "tagheadline">Fråga</th>';
        $html .= '<th class = "taganswers">Svar</th>';
        $html .= '<th class = "tagcomments">Kommentarer</th>';
        $html .= '</tr>';
        return $html;
    }



    /**
    * @return string $childrentext - htmltext for comments to comment-item
    */
    public function getChildrenText($val)
    {
        $answers = "";
        $comments = "";
        $children = $this->misc->findAllWhere("parentid = ?", $val->id);
        foreach ($children as $item) {
            if ($item->iscomment == 1) {
                $comments .= '<a href = "' . $this->misc->setUrlCreator("comm/view-one") . '/' . $item->id . '"><span class = "delagreen">' . $item->title . '</span></a>, ';
            } else {
                $answers .= '<a href = "' . $this->misc->setUrlCreator("comm/view-one") . '/' . $item->id . '"><span class = "delablue">' . $item->title . '</span></a>, ';
            }
        }
        $answers = rtrim($answers, ', ');
        $comments = rtrim($comments, ', ');
        $childrentext = "<td class = 'taganswers'>" . $answers . "</td><td class = 'comments'>" . $comments . "</td>";
        return $childrentext;
    }


    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $html = $this->getTableStart();

        $userhelp = new UserHelp($this->di);

        foreach ($this->tagset as $val) {
            $childrentext = $this->getChildrenText($val);
            $html .= "<tr>";
            $user = $userhelp->getOne($val->userid);
            $grav = $this->misc->getGravatar($user['email']);
            $acronym = $user['acronym'];
            $html .= '<td class = "itis"><span class="smaller em06">' . $acronym . '</span></td>';
            $html .= '<td class = "grav">' . $grav . '</td>';
            $html .= "<td class = 'text'><a href = '" . $this->misc->setUrlCreator("comm/view-one") . "/" . $val->id . "'><span class = 'delared'>" . $val->title . "</span></td>";
            $html .= $childrentext;
            $html .= '</tr>';
        }

        $html .= "</tbody></table>";

        return $html;
    }
}
