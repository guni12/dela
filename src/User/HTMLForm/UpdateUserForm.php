<?php

namespace Guni\User\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;
use \Guni\User\UserHelp;

/**
 * Example of FormModel implementation.
 */
class UpdateUserForm extends FormModel
{
    protected $user;
    protected $userhelp;
    protected $admin;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to update
     */
    public function __construct(DIInterface $di, $id)
    {
        parent::__construct($di);
        $this->userhelp = new UserHelp($di);
        $this->user = $this->userhelp->getUserItems($id);

        $sess = $this->di->get("session")->get("user");

        $checked = $sess['isadmin'] == 1 ? true : false;
        $this->admin = $sess['isadmin'] ? ["type" => "checkbox","checked"   => $checked,"label" => "Är admin"] : ["type" => "hidden", "value" => null];

        $this->aForm();
    }

    /**
     * Create the form.
     *
     */
    public function aForm()
    {
        $this->form->create(
            ["id" => __CLASS__,"legend" => "Uppdatera kontot",],
            ["id" => ["type" => "text","validation" => ["not_empty"],"readonly" => true,"value" => $this->user->id,],
            "created" => ["type" => "datetime","readonly" => true,"value" => $this->user->created,"label" => "Skapad",],
            "updated" => ["type" => "datetime","readonly" => true,"value" => $this->user->updated,"label" => "Uppdaterad",],
            "acronym" => ["type" => "text","validation" => ["not_empty"],"value" => $this->user->acronym,"class" => "form-control"],
            "email" => ["type" => "email","label" => "Epost","value" => $this->user->email,"validation" => ["email"],"class" => "form-control"],
            "profile" => ["type" => "text","label" => "Hemort","value" => $this->user->profile,"validation" => ["not_empty"],"wrapper-element-class" => "form-group","class" => "form-control",],
            "isadmin" => $this->admin,
            "newpassword" => ["type" => "text","validation" => ["not_empty"],"label"      => "Nytt lösenord","class" => "form-control"],
            "password-again" => ["type" => "text","validation" => ["match" => "newpassword"],"label" => "Nytt lösenord igen","class" => "form-control"],
            "submit" => ["type" => "submit","value" => "Spara","callback" => [$this, "callbackSubmit"]],
            "reset" => ["type" => "reset",],
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
        $now = date("Y-m-d");

        $newpassword      = $this->form->value("newpassword");
        $passwordAgain = $this->form->value("password-again");

        // Check password matches
        if ($newpassword !== $passwordAgain) {
            $this->form->rememberValues();
            $this->form->addOutput("Lösenorden stämde inte.");
            return false;
        }

        $user = new User();
        $user->setDb($this->di->get("db"));
        $user->find("id", $this->form->value("id"));
        $user->updated = $now;
        $user->acronym = $this->form->value("acronym");
        $user->email = $this->form->value("email");
        $user->isadmin = $this->form->value("isadmin");
        $user->profile = $this->form->value("profile");
        $user->setPassword($passwordAgain);
        $user->save();
        $this->di->get("response")->redirect("user/login");
    }
}
