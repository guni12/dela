<?php

namespace Guni\User;

use \Anax\Configure\ConfigureInterface;
use \Anax\Configure\ConfigureTrait;
use \Anax\DI\InjectionAwareInterface;
use \Anax\Di\InjectionAwareTrait;
use \Guni\User\HTMLForm\UserLoginForm;
use \Guni\User\HTMLForm\UserLogout;
use \Guni\User\HTMLForm\CreateUserForm;
use \Guni\User\HTMLForm\UpdateUserForm;
use \Guni\User\HTMLForm\DeleteUserForm;
use \Guni\User\ShowAllService;
use \Guni\User\UserHelp;

/**
 * A controller class.
 */
class UserController implements
    ConfigureInterface,
    InjectionAwareInterface
{
    use ConfigureTrait,
        InjectionAwareTrait;


    /**
    *
    * @return sessionobject
    */
    public function getSess()
    {
        $session = $this->di->get("session");
        $sess = $session->get("user");
        return $sess;
    }


    /**
     * Questionspage
     *
     * @return void
     */
    public function getIndex()
    {
        $text = new ShowAllService($this->di);
        $sess = $this->getSess();

        $data = $sess['isadmin'] ? ["content" => $text->getHTML(),] : ["content" => $text->getMembers()];

        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Alla medlemmar", "user/crud/default", $data);
    }


    /**
     * One Member Page
     *
     * @param integer $id Chosen User
     *
     * @return void
     */
    public function getPostOneUser($id)
    {
        $text = new ShowOneService($this->di, $id);
        $text = $text->getHTML();
        $data = ["content" => $text];

        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Användare " . $id, "user/crud/view-one", $data);
    }



    /**
     * Loginpage
     *
     * @return void
     */
    public function getPostLogin()
    {
        $form       = new UserLoginForm($this->di);
        $form->check();

        $extra = new ShowAllService($this->di);
        $extratext = $extra->getLoginText();

        $data = [
            "content" => $form->getHTML(),
            "text" => $extratext,
        ];

        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Logga in", "user/crud/login", $data);
    }



    /**
     * Logoutpage
     *
     * @return void
     */
    public function getPostLogout()
    {
        $text       = new UserLogout($this->di);

        $data = [
            "content" => $text->getHTML(),
        ];

        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Logga ut", "user/crud/logout", $data);
    }



    /**
     * Create User Page
     *
     * @return void
     */
    public function getPostCreateUser()
    {
        $form       = new CreateUserForm($this->di);
        $form->check();

        $data = [
            "content" => '<div class="col-lg-12 col-sm-12 col-xs-12">' . $form->getHTML() . '</div>',
        ];

        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Skapa användare", "user/crud/create", $data);
    }



    /**
     * Update Member
     * @param integer $id - Member to update
     *
     * @return void
     */
    public function getPostUpdateUser($id)
    {
        $sess = $this->getSess();
        $userid = isset($sess['id']) ? $sess['id'] : "";

        $url = $this->di->get("url");
        $delete = call_user_func([$url, "create"], "user/delete");

        $text = "";
        if ($id > 0) {
            $text = '<p><span class="button"><a href="';
            $text .= $delete . '/' . $id . '">Ta bort kontot</a></span></p>';
        }

        if ($sess['isadmin'] == 1 || $userid == $id) {
            $form = new UpdateUserForm($this->di, $id);
            $form->check();

            $data = [
                "form" => '<div class="col-lg-12 col-sm-12 col-xs-12">' . $form->getHTML() . '</div>' ,
                "text" => $text,
            ];
        } else {
            $data = [
                "form" => '<div class="col-lg-12 col-sm-12 col-xs-12">' . "Inte ditt id. Sorry!" . '</div>',
            ];
        }

        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Uppdatera användaren", "user/crud/update", $data);
    }



    /**
     * Delete member
     * @param integer - Member to delete
     *
     * @return void
     */
    public function getPostDeleteUser($id)
    {
        $sess = $this->getSess();
        $userid = isset($sess['id']) ? $sess['id'] : "";

        if ($sess['isadmin'] == 1 || $userid == $id) {
            $form       = new DeleteUserForm($this->di, $id);
            $form->check();

            $data = [
                "form" => '<div class="col-lg-12 col-sm-12 col-xs-12">' . $form->getHTML() . '</div>',
            ];
        } else {
            $data = [
                "form" => $text = '<div class="col-lg-12 col-sm-12 col-xs-12">' . "Inte ditt id. Sorry!" . '</div>',
            ];
        }
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Avanmäl användare", "user/crud/delete", $data);
    }
}
