<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\Misc;
use \Guni\User\UserHelp;

/**
 * Form to update an item.
 */
class ShowAllService
{
    /**
    * @var array $comments, all comments.
    */
    protected $comments;
    protected $sess;
    protected $users;
    protected $user;
    protected $userhelp;
    protected $isadmin;
    protected $di;
    protected $answersct;
    protected $commentsct;
    protected $misc;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->misc = new Misc($di);
        $this->userhelp = new UserHelp($di);
        $this->comments = $this->misc->getAll();
        $session = $this->di->get("session");
        $this->sess = $session->get("user");
        $this->sess = isset($this->sess) ? $this->sess : null;
        $this->users = $this->userhelp->getAllUsers();
        $this->user = $this->userhelp->getOne($this->sess['id']);
        $this->isadmin = $this->sess['isadmin'] === 1 ? true : false;
        $this->answersct = 0;
        $this->commentsct = 0;
    }


    /**
     * Returns when created or updated
     *
     * @param object $item
     * @return string htmlcode
     */
    public function getWhen($item)
    {
        $when = "";
        if ($item->updated) {
            $when .= '<span class="smaller">Ändrad: ' . $item->updated;
        } else {
            $when .= '<span class="smaller">' . $item->created;
        }
        return $when;
    }



    /**
    * @param object $commcomments - comments to at parentcomment
    */
    public function countResponses($commcomments)
    {
        foreach ($commcomments as $value) {
            $this->commentsct = $value->iscomment > 0 ? $this->commentsct + 1 : $this->commentsct;
            $this->answersct = $value->iscomment <= 0 ? $this->answersct + 1 : $this->answersct;
        }
    }


    /**
     * Returns html for each item
     *
     * @param object $item
     * @param string $viewone
     *
     * @return string htmlcode
     */
    public function getValHtml(Comm $item, $email, $acronym)
    {
        $answers = "";
        $comments = "";

        $when = $this->getWhen($item);
        $commcomments = $this->misc->findAllWhere("parentid = ?", $item->id);

        $this->countResponses($commcomments);

        
        if ($this->answersct > 0) {
            $answers = $this->answersct . ' svar';
            $answers .= $this->commentsct > 0 || $item->points > 0 ? ", " : "";
            $comments = $this->commentsct > 0 ? $this->commentsct . " kommentarer" : "";
        }

        $comma = $this->commentsct > 0 ? ", " : "";
        $points = $item->points !== null && $item->points > 0 ? $comma . 'rank: ' . $item->points : "";
        $numbers = [$answers, $comments, $points];
        $user = [$email, $acronym];

        return $this->misc->getTheText($item, $numbers, $when, $user, $this->isadmin);
    }


    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $create = $this->misc->setUrlCreator("comm/create");
        $del = $this->misc->setUrlCreator("comm/admindelete");

        $loggedin = $this->misc->getLoginLink($create, $del, $this->sess['id'], $this->isadmin);

        $html = '<div class="col-lg-12 col-sm-12 col-xs-12"><div class="">

        <h3>Gruppinlägg <span class="small">' . $loggedin . '</span></h3>
        <hr />';

        $html .= '<table class = "member tagpage"><tr><th class="allmember">Medlem</th><th class="alltitle">Rubrik</th><th class="asked">Frågad</th><th class="respons">Respons</th></tr>';

        foreach ($this->comments as $value) {
            if ((int)$value->parentid > 0) {
                continue;
            }
            $curruser = $this->userhelp->getOne($value->userid);
            $html .= $this->getValHtml($value, $curruser['email'], $curruser['acronym']);
        }
        $html .= '</table>';
        $html .= '</div></div>';
        return $html;
    }
}
