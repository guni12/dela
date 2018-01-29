<?php

namespace Guni\User;

use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Form to update an item.
 */
class ShowAllService
{
    /**
    * @var array $comments, all comments.
    */
    protected $sess;
    protected $users;
    protected $di;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->users = $this->getAll();
        $session = $this->di->get("session");
        $this->sess = $session->get("user");
        $addsess = isset($this->sess) ? $this->sess : null;
        $this->sess = $addsess;
    }

    /**
     * Get details on comments.
     *
     *
     * @return All comments
     */
    public function getAll()
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        return $user->findAll();
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


    public function getMembers()
    {
        $html = '<div class = "col-sm-12 col-xs-12 col-lg-12 col-md-12">';
        $html .= '<div class="flex">';
        $comm = $this->di->get("commController");
        $one = $this->setUrlCreator("user/view-one/");

        foreach ($this->users as $value) {
            $grav = $comm->getGravatar($value->email, 50);
            $thisone = $one . "/" . $value->id;
            $grav = "<img src='" . $grav . "' /><br />";
            $html .= '<div class="inner">';
            $html .= '<div class="left">';
            $html .= $grav;
            $html .= '</div><div class="right">';
            $html .= '<a href="' . $thisone . '">' . $value->acronym;
            $html .= '</a><br />' . $value->profile;
            $html .= '<br /></div></div>';
        }
        $html .= '</div>';
        return $html;
    }


    /**
    * @return htmltext to start table
    */
    public function tableStart($create, $del)
    {
        $html = '<h1>Alla medlemmar</h1>';
        $html .= '<p><span class="button"><a href="' . $create . '">Lägg Till Medlem</a></span>';
        $html .= ' | <span class="button"><a href="' . $del . '">Ta bort Medlem</a></span></p>';


        $html .= '<table class = "member tagpage"><tr>
        <th class="userid">Id</th>
        <th class="acronym">Acronym</th>
        <th class="useremail">Email</th>
        <th class="profile">Profil</th>
        <th class="isadmin">Adm</th>
        <th class="created">Skapades</th>
        <th class="updated">Uppdaterades</th>
        </tr>';
        return $html;
    }


    public function getHTML()
    {
        $html = $this->getMembers();

        $create = $this->setUrlCreator("user/admincreate");
        $adminupdate = $this->setUrlCreator("user/adminupdate");
        $del = $this->setUrlCreator("user/admindelete");

        $html .= $this->tableStart($create, $del);

        foreach ($this->users as $value) {
            $html .= '<tr><td>';
            $html .= '<a href="' . $adminupdate . '/' . $value->id . '">' . $value->id . '</a></td>';
            $html .= '<td>' . $value->acronym . '</td>';
            $html .= '<td>' . $value->email . '</td>';
            $html .= '<td>' . $value->profile . '</td>';
            $html .= '<td>' . $value->isadmin . '</td>';
            $html .= '<td>' . $value->created . '</td>';
            $html .= '<td>' . $value->updated . '</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }


    public function getLoginText()
    {
        $userid = isset($this->sess['id']) ? $this->sess['id'] : null;
        $isadmin = isset($this->sess['isadmin']) && $this->sess['isadmin'] == 1 ? $this->sess['isadmin'] : null;

        $url = $this->di->get("url");
        $members = call_user_func([$url, "create"], "user");
        $update = call_user_func([$url, "create"], "user/update");
        $delete = call_user_func([$url, "create"], "user/delete");
        $create = call_user_func([$url, "create"], "user/create");

        $text = '<p><span class="button"><a href="' . $create . '">Skapa ett nytt konto</a></span>';
        if ($isadmin == 1) {
            $text .= ' | <span class="button"><a href="' . $members . '">Till Admin</a></span></p>';
        } else if ((int)$userid > 0) {
            $text .= ' | <span class="button"><a href="' . $update . '/' . $userid . '">';
            $text .= 'Redigera ditt konto</a></span><br /><span class="button">';
            $text .= '<a href="' . $delete . '/' . $userid . '">Ta bort ditt konto</a></span></p>';
        }
        $text .= '</div>';
        return $text;
    }
}
