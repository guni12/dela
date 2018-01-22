<?php

namespace Guni\Comments\HTMLForm;

use \Guni\Comments\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;

/**
 * Form to create an item.
 */
class CreateCommForm extends FormModel
{
    protected $tags;
    protected $headline;
    protected $iscomment;


    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param boolean             $iscomment - if comment
     * @param integer             $id - current userid from session
     * @param integer             $parentid - if answer or comment
     */
    public function __construct(DIInterface $di, $iscomment, $id = null, $parentid = null)
    {
        parent::__construct($di);
        $this->iscomment = $iscomment;
        
        $this->aForm($id, $parentid);
    }


    /**
     * Get details on item to load form with.
     *
     * @param integer $id get details on item with id.
     *
     * @return Comm
     */
    public function getCommDetails($parentid)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $comm->find("id", $parentid);
        return $comm;
    }

    /**
    * @param integer $parentid - id of parentcomment
    *
    * @return array $dropdown - for the form
    */
    public function getDropdown($parentid)
    {
        if ($parentid > 0) {
            if ($this->iscomment == 1) {
                $dropdown = [
                    "type"        => "hidden",
                    "value"       => "comment",
                ];
                $this->headline = "Skriv en kommentar";
            } else {
                $dropdown = [
                    "type"        => "hidden",
                    "value"       => "answer",
                ];
                $this->headline = "Skriv ett svar";
            }
        } else {
            $dropdown = [
                "type"        => "select-multiple",
                "label"       => "Taggar, minst en:",
                "description" => "Håll ner Ctrl (windows) / Command (Mac) knapp för att välja flera taggar.<br />Default tagg är Elbil.",
                "size"        => 5,
                "options"     => [
                    "elcar" => "elbil",
                    "safety" => "säkerhet",
                    "light"  => "belysning",
                    "heat"   => "värme"
                ],
            ];
            $this->headline = "Gör ett inlägg";
            $this->iscomment = null;
        }

        return $dropdown;
    }


    /**
    * @return string $placeholder - placeholdertext
    */
    public function getPlaceholder()
    {
        $placeholder = 'Image: ![alt text](https://somewhere.com/img.jpg "Text") | ';
        $placeholder .= '*italics* | **emphasis** | [Link](https://www.somewhere.com) | ';
        $placeholder .= ' > Blockquotes';
        return $placeholder;
    }


    /**
     * Create the form.
     *
     */
    public function aForm($id, $parentid)
    {
        $dropdown = $this->getDropdown($parentid);
        $this->form->create(
            [   "id" => __CLASS__,
                "legend" => $this->headline,
                "wmd" => "wmd-button-bar",
                "preview" => "wmd-preview",
            ],
            [
                "title" => [
                    "type"  => "text",
                    "label" => "Titel",
                    "validation" => ["not_empty"],
                    "wrapper-element-class" => "form-group",
                    "class" => "form-control"
                ],
                "id" => [
                    "type"  => "hidden",
                    "value" => $id
                ],
                "userid" => [
                    "type"  => "hidden",
                    "value" => $id,
                ],
                "parentid" => [
                    "type"  => "hidden",
                    "value" => $parentid,
                ],
                "comment" => [
                    "type"  => "textarea",
                    "label" => "Text",
                    "id" => "wmd-input",
                    "placeholder" => $this->getPlaceholder(),
                    "validation" => ["not_empty"],
                    "wrapper-element-class" => "form-group",
                    "class" => "form-control wmd-input",
                ],
                "tags" => $dropdown,
                "iscomm" => [
                    "type"  => "hidden",
                    "value" => $this->iscomment,
                ],
                "submit" => [
                    "type" => "submit",
                    "value" => "Spara",
                    "class" => "btn btn-default",
                    "callback" => [$this, "callbackSubmit"]
                ],
            ]
        );
    }


    /**
    *
    */
    public function tagsToArray($tags)
    {
        $elcar = is_array($tags) && in_array("elcar", $tags) ? "elcar" : null;
        $safety = is_array($tags) && in_array("safety", $tags) ? "safety" : null;
        $light = is_array($tags) && in_array("light", $tags) ? "light" : null;
        $heat = is_array($tags) && in_array("heat", $tags) ? "heat" : null;

        $elcar = ($elcar == null && $safety == null && $light == null && $heat == null) ? "elcar" : $elcar;

        return [$elcar, $safety, $light, $heat];
    }


    /**
    * adds array through frontmatter to $comment
    * @param $tags - input from form
    *
    * @return $tags - if key "tags" it must have tags
    */
    public function handleTags($tags)
    {
        $tags = ($tags == "comment" || $tags == "answer") ? $tags : $this->tagsToArray($tags);
        return $tags;
    }


    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return boolean true if okey, false if something went wrong.
     */
    public function callbackSubmit()
    {
        $textfilter = $this->di->get("textfilter");

        $userController = $this->di->get("userController");
        $userdetails = $userController->getOne($this->form->value("id"));
        $parses = ["yamlfrontmatter", "shortcode", "markdown", "titlefromheader"];
        $comment = $textfilter->parse($this->form->value("comment"), $parses);
        $comment->frontmatter['title'] = $this->form->value("title");

        $this->form->rememberValues();

        $tags = $this->form->value("tags");
        $comment->frontmatter['tags'] = $this->handleTags($tags);

        $comment = json_encode($comment);

        $now = date("Y-m-d H:i:s");

        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $comm->title = $this->form->value("title");
        $comm->userid = $this->form->value("userid");
        $comm->parentid = $this->form->value("parentid");
        $comm->iscomment = $this->form->value("iscomm");
        $comm->comment = $comment;
        $comm->created = $now;
        $comm->save();

        $this->form->rememberValues();
        
        $back = (int)$this->form->value("parentid") > 0 ? "/view-one/" . $this->form->value("parentid") : "";

        $pagerender = $this->di->get("pageRender");
        $pagerender->redirect("comm" . $back);
    }
}
