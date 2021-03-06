<?php

namespace Guni\User\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Example of FormModel implementation.
 */
class CreateUserForm extends FormModel
{
    protected $sess;
    protected $isadmin;

    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        parent::__construct($di);
        $this->sess = $this->di->get("session")->get("user");
        $this->isadmin = $this->sess['isadmin'];

        $this->aForm();
    }



    /**
     * Create the form.
     *
     */
    public function aForm()
    {
        $admin = $this->isadmin ? ["type" => "checkbox","label" => "Är admin"] : ["type" => "hidden","value" => null];

        $this->form->create(
            ["id" => __CLASS__,"legend" => "Ställ en fråga",],
            ["acronym" => ["type" => "text","label" => "Användarnamn","wrapper-element-class" => "form-group","class" => "form-control",],
            "email" => ["type" => "email","label" => "Epost","validation" => ["email","not_empty"],"wrapper-element-class" => "form-group","class" => "form-control",],
            "profile" => ["type" => "text","label" => "Hemort","validation" => ["not_empty"],"wrapper-element-class" => "form-group","class" => "form-control",],
            "password" => ["type" => "password","label" => "Lösenord","wrapper-element-class" => "form-group","class" => "form-control",],
            "password-again" => ["type" => "password","validation" => ["match" => "password"],"label" => "Lösenord igen","wrapper-element-class" => "form-group","class" => "form-control",],
            "isadmin" => $admin,
            "submit" => ["type" => "submit","value" => "Lägg till","callback" => [$this, "callbackSubmit"]],
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
        // Get values from the submitted form
        $acronym       = $this->form->value("acronym");
        $password      = $this->form->value("password");
        $passwordAgain = $this->form->value("password-again");
        $now = date("Y-m-d h:i:s");

        // Check password matches
        if ($password !== $passwordAgain) {
            $this->form->rememberValues();
            $this->form->addOutput("Password did not match.");
            return false;
        }

        $user = new User();
        $user->setDb($this->di->get("db"));
        $user->acronym = $acronym;
        $user->email = $this->form->value("email");
        $user->profile = $this->form->value("profile");
        $user->setPassword($password);
        $user->isadmin = $this->form->value("isadmin");
        $user->created = $now;
        $user->save();

        $this->di->get("response")->redirect("user/login");
        return true;
    }
}
