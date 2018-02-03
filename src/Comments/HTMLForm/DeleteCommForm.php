<?php

namespace Guni\Comments\HTMLForm;

use \Guni\Comments\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\Misc;

/**
 * Form to delete an item.
 */
class DeleteCommForm extends FormModel
{
    protected $userid;
    protected $misc;
    protected $comm;
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
        $this->session = $this->di->get("session");
        $sess = $this->session->get("user");

        $this->misc = new Misc($di);
        $this->comm = $this->misc->getItemDetails($id);

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
                "legend" => "Alla kommentarer tas också bort",
            ],
            [
                "userid" => [
                    "type"        => "hidden",
                    "value"       => $this->comm->userid,
                ],

                "id" => [
                    "type"        => "text",
                    "readonly"    => true,
                    "value"       => $this->comm->id,
                    "label"       => "Ta bort inlägget",
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
    public function getAllPosts()
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));

        $posts = ["-1" => "Välj en text..."];
        foreach ($comm->findAll() as $obj) {
            $posts[$obj->id] = "{$obj->title} ({$obj->id})";
        }

        return $posts;
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
                "legend" => "Alla inlägg:",
            ],
            [
                "select" => [
                    "type"        => "select",
                    "label"       => "Poster:",
                    "options"     => $this->getAllPosts(),
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
        $all = $this->misc->getAll();
        $objects = "";
        $one = $this->isadmin ? $this->form->value("select") : $this->form->value("id");

        foreach ($all as $obj) {
            if ($obj->parentid && $obj->parentid == $one) {
                $comm->find("id", $obj->id);
                $objects .= $comm->title . ", ";
                $comm->delete();
            }
        }

        $this->comm->find("id", $this->form->value("id"));
        $objects .= $this->comm->title;
        $this->comm->delete();
        $pagerender = $this->di->get("pageRender");
        $pagerender->redirect("comm");
    }
}
