<?php

namespace Guni\Comments\HTMLForm;

use \Guni\Comments\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;

/**
 * Form to delete an item.
 */
class AdminDeleteCommForm extends FormModel
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
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return boolean true if okey, false if something went wrong.
     */
    public function callbackSubmit()
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $one = $this->form->value("select");

        $all = $comm->findAll();
        $objects = "";

        foreach ($all as $obj) {
            if ($obj->parentid && $obj->parentid == $one) {
                echo 'Ja: ' . $obj->parentid . ' kastar ' . $obj->id . '<br />';
                $comm->find("id", $obj->id);
                $objects .= $comm->title . ", ";
                $comm->delete();
            }
        }

        $comm->find("id", $this->form->value("select"));
        $titles = $objects . $comm->title;
        $comm->delete();
        $this->form->addOutput($titles . ": kastad.");

        $pagerender = $this->di->get("pageRender");
        $pagerender->redirect("comm");
    }
}
