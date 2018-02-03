<?php

namespace Guni\User\HTMLForm;

use \Guni\Comments\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;
use \Guni\User\UserHelp;

/**
 * Form to delete an item.
 */
class DeleteUserForm extends FormModel
{
    protected $userhelp;
    protected $user;
    protected $session;
    protected $isadmin;



    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di, $id)
    {
        parent::__construct($di);
        $this->userhelp = new UserHelp($di);
        $this->session = $this->di->get("session");
        $sess = $this->session->get("user");

        $this->user = $this->userhelp->getUserItems($id);
        $this->isadmin = $sess['isadmin'];

        $this->isadmin ? $this->formAdmin() : $this->formUser();
    }


    /**
    *
    *
    */
    public function formUser()
    {
        $this->form->create(
            [
                "id" => __CLASS__,
                "legend" => $this->user->acronym,
            ],
            [
                "id" => [
                    "type"        => "text",
                    "readonly"    => true,
                    "value"       => $this->user->id,
                    "label"       => "Avanmäl här",
                ],

                "submit" => [
                    "type" => "submit",
                    "value" => "Ta bort",
                    "callback" => [$this, "callbackSubmit"]
                ],
            ]
        );
    }


    /**
    *
    *
    */
    public function formAdmin()
    {
        $this->form->create(
            [
                "id" => __CLASS__,
                "legend" => "Avanmäl här",
            ],
            [
                "select" => [
                    "type"        => "select",
                    "label"       => "Användare:",
                    "options"     => $this->userhelp->getSelectUsers(),
                ],

                "submit" => [
                    "type" => "submit",
                    "value" => "Ta bort",
                    "callback" => [$this, "callbackSubmit"]
                ],
            ]
        );
    }



    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return boolean true if okey, false if something went wrong.
     */
    public function callbackSubmit()
    {
        $user = new User();
        $user->setDb($this->di->get("db"));

        $formid = $this->isadmin ? $this->form->value("select") : $this->form->value("id");

        $user->find("id", $formid);
        $user->delete();

        $this->isadmin == 1 ?  : $this->session->delete('user');
        $this->isadmin == 1 ? $this->di->get("response")->redirect("user") : $this->di->get("response")->redirect("user/login");
    }
}
