<?php

namespace Guni\User\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Example of FormModel implementation.
 */
class UpdateUserForm extends FormModel
{
    protected $user;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to update
     */
    public function __construct(DIInterface $di, $id)
    {
        parent::__construct($di);
        $this->user = $this->getUserDetails($id);

        $this->aForm();
    }

    /**
     * Create the form.
     *
     */
    public function aForm()
    {
        $this->form->create(
            [
                "id" => __CLASS__,
                "legend" => "Uppdatera ditt konto",
            ],
            [
                "id" => [
                    "type" => "text",
                    "validation" => ["not_empty"],
                    "readonly" => true,
                    "value" => $this->user->id,
                ],


                "acronym" => [
                    "type" => "text",
                    "validation" => ["not_empty"],
                    "value" => $this->user->acronym,
                    "class" => "form-control"
                ],

                "email" => [
                    "type"        => "email",
                    "label"       => "Epost",
                    "value" => $this->user->email,
                    "validation" => ["email"],
                    "class" => "form-control"
                ],

                "profile" => [
                    "type"        => "text",
                    "label"       => "Hemort",
                    "value" => $this->user->profile,
                    "validation" => ["not_empty"],
                    "wrapper-element-class" => "form-group",
                    "class" => "form-control",
                ],

                "newpassword" => [
                    "type" => "text",
                    "validation" => ["not_empty"],
                    "label"      => "Nytt lösenord",
                    "class" => "form-control"
                ],

                "password-again" => [
                    "type"        => "text",
                    "validation" => [
                        "match" => "newpassword"
                    ],
                    "label"      => "Nytt lösenord igen",
                    "class" => "form-control"
                ],

                "submit" => [
                    "type" => "submit",
                    "value" => "Spara",
                    "callback" => [$this, "callbackSubmit"]
                ],

                "reset" => [
                    "type"      => "reset",
                ],
            ]
        );
    }




    /**
     * Get details on item to load form with.
     *
     * @param integer $id get details on item with id.
     *
     * @return User
     */
    public function getUserDetails($id)
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        $user->find("id", $id);
        return $user;
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
        $user->profile = $this->form->value("profile");
        $user->setPassword($passwordAgain);
        $user->save();
        $this->di->get("response")->redirect("user/login");
    }
}
