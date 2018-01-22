<?php

namespace Guni\User\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Example of FormModel implementation.
 */
class AdminUpdateUserForm extends FormModel
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

        $this->aForm($id);
    }

        /**
     * Create the form.
     *
     */
    public function aForm($id)
    {
        $checked = false;
        var_dump($this->user->isadmin);

        if ($this->user->isadmin == 1) {
            $checked = true;
        } else {
            $checked = false;
        }

        $this->form->create(
            [
                "id" => __CLASS__,
                "legend" => "Uppdatera användarinfo",
            ],
            [
                "id" => [
                    "type" => "text",
                    "validation" => ["not_empty"],
                    "readonly" => true,
                    "value" => $this->user->id,
                ],

                "created" => [
                    "type" => "datetime",
                    "readonly" => true,
                    "value" => $this->user->created,
                    "label"      => "Skapad",
                ],

                "updated" => [
                    "type" => "datetime",
                    "readonly" => true,
                    "value" => $this->user->updated,
                    "label"      => "Uppdaterad",
                ],

                "active" => [
                    "type" => "datetime",
                    "readonly" => true,
                    "value" => $this->user->active,
                    "label"      => "Aktiv",
                ],

                "acronym" => [
                    "type" => "text",
                    "validation" => ["not_empty"],
                    "value" => $this->user->acronym,
                ],

                "email" => [
                    "type"        => "email",
                    "label"       => "Epost",
                    "value" => $this->user->email,
                    "validation" => ["email"],
                ],

                "isadmin" => [
                    "type" => "checkbox",
                    "checked"   => $checked,
                    "label"      => "Är admin",
                ],

                "newpassword" => [
                    "type" => "text",
                    "label"      => "Nytt lösenord",
                ],

                "password-again" => [
                    "type"        => "text",
                    "validation" => [
                        "match" => "newpassword"
                    ],
                    "label"      => "Nytt lösenord igen",
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
        $user = new User();
        $user->setDb($this->di->get("db"));
        $user->find("id", $this->form->value("id"));
        $user->updated = date("Y-m-d");

        // Check password matches
        if ($this->form->value("newpassword") !== $this->form->value("password-again")) {
            $this->form->rememberValues();
            $this->form->addOutput("Lösenorden stämde inte.");
            return false;
        }


        if (isset($this->form->value("acronym"))) {
            $user->acronym = $this->form->value("acronym");
        }
        if (isset($this->form->value("email"))) {
            $user->email = $this->form->value("email");
        }
        if (isset($this->form->value("password-again"))) {
            $user->setPassword($this->form->value("password-again"));
        }
        $user->isadmin = $this->form->value("isadmin");

        $user->save();
        $this->di->get("response")->redirect("user");
    }
}
