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
    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to update
     */
    public function __construct(DIInterface $di, $id)
    {
        parent::__construct($di);
        $user = $this->getUserDetails($id);

        $checked = false;

        if ($user->isadmin == 1) {
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
                    "value" => $user->id,
                ],

                "created" => [
                    "type" => "datetime",
                    "readonly" => true,
                    "value" => $user->created,
                    "label"      => "Skapad",
                ],

                "updated" => [
                    "type" => "datetime",
                    "readonly" => true,
                    "value" => $user->updated,
                    "label"      => "Uppdaterad",
                ],

                "active" => [
                    "type" => "datetime",
                    "readonly" => true,
                    "value" => $user->active,
                    "label"      => "Aktiv",
                ],

                "acronym" => [
                    "type" => "text",
                    "validation" => ["not_empty"],
                    "value" => $user->acronym,
                ],

                "email" => [
                    "type"        => "email",
                    "label"       => "Epost",
                    "value" => $user->email,
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
        //var_dump($user->isadmin);
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
        $email = $this->form->value("email");
        $acronym = $this->form->value("acronym");

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
        if (isset($acronym)) {
            $user->acronym = $acronym;
        }
        if (isset($email)) {
            $user->email = $this->form->value("email");
        }
        if (isset($newpassword)) {
            $user->setPassword($passwordAgain);
        }
        $user->isadmin = $this->form->value("isadmin");

        $user->save();
        $this->di->get("response")->redirect("user");
    }
}
