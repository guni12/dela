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
use \Guni\User\HTMLForm\AdminCreateUserForm;
use \Guni\User\HTMLForm\AdminUpdateUserForm;
use \Guni\User\HTMLForm\AdminDeleteUserForm;
use \Guni\User\ShowAllService;

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
     * Sends data to view
     *
     * @param string $title
     * @param string $crud, path to view
     * @param array $data, htmlcontent to view
     */
    public function toRender($title, $crud, $data)
    {
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");
        $view->add($crud, $data);
        $tempfix = "";
        $pageRender->renderPage($tempfix, ["title" => $title]);
    }


    /**
     * Questionspage
     *
     * @return void
     */
    public function getIndex()
    {
        $title      = "Alla medlemmar";
        $sess = $this->getSess();
        $crud = "user/crud/default";

        if ($sess['isadmin'] == 1) {
            $text = new ShowAllService($this->di);
            $data = [
                "users" => $text->getHTML(),
            ];
            $crud = "user/crud/admin";
        } else {
            $text = new ShowAllService($this->di);
            $data = [
                "content" => $text->getMembers(),
            ];
        }
        $this->toRender($title, $crud, $data);
    }

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
     * One Member Page
     *
     * @param integer $id Chosen User
     *
     * @return void
     */
    public function getPostOneUser($id)
    {
        $title = "Användare " . $id;
        $sess = $this->getSess();


        $text = new ShowOneService($this->di, $id);
        $text = $text->getHTML();
        $data = ["content" => $text];


        $crud = "user/crud/view-one";
        $this->toRender($title, $crud, $data);
    }



    /**
     * Loginpage
     *
     * @return void
     */
    public function getPostLogin()
    {
        $title      = "Logga in";

        $form       = new UserLoginForm($this->di);

        $form->check();

        $extra = new ShowAllService($this->di);
        $extratext = $extra->getLoginText();

        $data = [
            "content" => $form->getHTML(),
            "text" => $extratext,
        ];

        $crud = "user/crud/login";
        $this->toRender($title, $crud, $data);
    }



    /**
     * Logoutpage
     *
     * @return void
     */
    public function getPostLogout()
    {
        $title      = "Logga ut";
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");
        $text       = new UserLogout($this->di);

        $data = [
            "content" => $text->getHTML(),
        ];

        $crud = "user/crud/logout";
        $this->toRender($title, $crud, $data);
    }



    /**
     * Create User Page
     *
     * @return void
     */
    public function getPostCreateUser()
    {
        $title      = "Skapa användare";
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");
        $form       = new CreateUserForm($this->di);

        $form->check();

        $data = [
            "content" => $form->getHTML(),
        ];

        $crud = "user/crud/create";
        $this->toRender($title, $crud, $data);
    }



    /**
     * Update Member
     * @param integer $id - Member to update
     *
     * @return void
     */
    public function getPostUpdateUser($id)
    {
        $title      = "Uppdatera användaren";

        $sess = $this->getSess();
        $userid = isset($sess['id']) ? $sess['id'] : "";

        $url = $this->di->get("url");
        $delete = call_user_func([$url, "create"], "user/delete");

        $text = "";
        if ($id > 0) {
            $text = '<p><span class="button"><a href="';
            $text .= $delete . '/' . $userid . '">Ta bort ditt konto</a></span></p>';
        }

        if ($userid == $id) {
            $form       = new UpdateUserForm($this->di, $id);
            $form->check();

            $data = [
                "form" => $form->getHTML(),
                "text" => $text,
            ];
        } else {
            $data = [
                "form" => "Inte ditt id. Sorry!",
            ];
        }

        $crud = "user/crud/update";
        $this->toRender($title, $crud, $data);
    }



    /**
     * Delete member
     * @param integer - Member to delete
     *
     * @return void
     */
    public function getPostDeleteUser($id)
    {
        $title      = "Avanmäl användare";
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");

        $sess = $this->getSess();
        $userid = isset($sess['id']) ? $sess['id'] : "";

        if ($userid == $id) {
            $form       = new DeleteUserForm($this->di, $id);
            $form->check();

            $data = [
                "form" => $form->getHTML(),
            ];
        } else {
            $data = [
                "form" => "Inte ditt id. Sorry!",
            ];
        }

        $crud = "user/crud/delete";
        $this->toRender($title, $crud, $data);    
    }




    /**
     * Create User for Admin
     *
     * @return void
     */
    public function getPostAdminCreateUser()
    {
        $title      = "Skapa användare_admin";
        $sess = $this->getSess();
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");

        if ($sess['isadmin'] == 1) {
            $form       = new AdminCreateUserForm($this->di);

            $form->check();

            $data = [
                "content" => $form->getHTML(),
            ];
        } else {
            $data = [
                "content" => "Enbart för admin. Sorry!",
            ];
        }

        $crud = "user/crud/admincreate";
        $this->toRender($title, $crud, $data);
    }


    /**
     * Update Member For Admin
     * @param integer $id - Member to update
     *
     * @return void
     */
    public function getPostAdminUpdateUser($id)
    {
        $title      = "Uppdatera användaren";
        $sess = $this->getSess();
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");

        if ($sess['isadmin'] == 1) {
            $form       = new AdminUpdateUserForm($this->di, $id);

            $form->check();

            $data = [
                "form" => $form->getHTML(),
            ];
        } else {
            $data = [
                "form" => "Enbart för admin. Sorry!",
            ];
        }

        $crud = "user/crud/adminupdate";
        $this->toRender($title, $crud, $data);       
    }



    /**
     * Delete Member For Admin
     * @param integer $id - Member to delete
     *
     * @return void
     */
    public function getPostAdminDeleteUser()
    {
        $title      = "Avanmäl användare";
        $sess = $this->getSess();
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");

        if ($sess['isadmin'] == 1) {
            $form       = new AdminDeleteUserForm($this->di);

            $form->check();

            $data = [
                "form" => $form->getHTML(),
            ];
        } else {
            $data = [
                "form" => "Enbart för admin. Sorry!",
            ];
        }

        $crud = "user/crud/admindelete";
        $this->toRender($title, $crud, $data);
    }


    public function getAllUsers()
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        $users = $user->findAll();
        return $users;
    }


    public function getOne($id)
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        $one = $user->find("id", $id);
        //var_dump($id);

        if ($one) {
            $data = [
                "email" => $one->email,
                "id" => $one->id,
                "acronym" => $one->acronym,
                "active" => $one->active,
                "created" => $one->created,
                "isadmin" => $one->isadmin,
            ];
        } else {
            $data = [];
        }

        return $data;
    }
}
