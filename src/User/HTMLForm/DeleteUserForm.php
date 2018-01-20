<?php

namespace Guni\User\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Form to delete an item.
 */
class DeleteUserForm extends FormModel
{
    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di, $id)
    {
        parent::__construct($di);
        $user = $this->getUserDetails($id);

        $this->form->create(
            [
                "id" => __CLASS__,
                "legend" => $user->acronym,
            ],
            [
                "id" => [
                    "type"        => "text",
                    "readonly"    => true,
                    "value"       => $id,
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
     * Get all items as array suitable for display in select option dropdown.
     *
     * @return array with key value of all items.
     */
    protected function getUserDetails($id)
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
        $acronym;
        $user = new User();
        $user->setDb($this->di->get("db"));
        $user->find("id", $this->form->value("id"));
        $acronym = $user->acronym;
        $user->delete();

        $session = $this->di->get("session");
        $session->delete('user');
        //$this->form->addOutput($acronym . ": kastad.");
        $this->di->get("response")->redirect("user/login");
    }
}
