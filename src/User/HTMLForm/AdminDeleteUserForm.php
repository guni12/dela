<?php

namespace Guni\User\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Form to delete an item.
 */
class AdminDeleteUserForm extends FormModel
{
    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        parent::__construct($di);
        $this->form->create(
            [
                "id" => __CLASS__,
                "legend" => "Avanmäl här",
            ],
            [
                "select" => [
                    "type"        => "select",
                    "label"       => "Användare:",
                    "options"     => $this->getAllUsers(),
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
     * Get all items as array suitable for display in select option dropdown.
     *
     * @return array with key value of all items.
     */
    protected function getAllUsers()
    {
        $user = new User();
        $user->setDb($this->di->get("db"));

        $users = ["-1" => "Välj en användare..."];
        foreach ($user->findAll() as $obj) {
            $users[$obj->id] = "{$obj->acronym} ({$obj->id})";
        }

        return $users;
    }



    /**
     * deletes user with chosen id
     */
    public function callbackSubmit()
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        $user->find("id", $this->form->value("select"));
        $user->delete();
        $this->di->get("response")->redirect("user");
    }
}
