<?php

namespace Guni\Comments\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;

/**
 * Example of FormModel implementation.
 */
class UpdateCommForm extends FormModel
{
    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to update
     */
    public function __construct(DIInterface $di, $id, $sessid)
    {
        parent::__construct($di);
        $comm = $this->getCommDetails($id);

        $comt = $this->decode($comm->comment);
        $tags = $this->decodeTags($comm->comment);
        //var_dump($comt);

        $this->aForm($id, $sessid, $comm, $comt, $tags);
    }


    /**
    * Converts json-string back to variables
    *
    * @param string $fromjson the jsoncode
    * @return the extracted comment-text
    */
    public function decode($fromjson)
    {
        $textfilter = $this->di->get("textfilter");
        //var_dump($fromjson);
        $toparse = json_decode($fromjson);
        $comt = $textfilter->parse($toparse->text, ["yamlfrontmatter", "shortcode", "markdown", "titlefromheader"]);
        $comt = strip_tags($comt->text);
        return $comt;
    }


    /**
    * Converts json-string back to variables
    *
    * @param string $fromjson the jsoncode
    * @return array | the extracted tags
    */
    public function decodeTags($fromjson)
    {
        $array = json_decode($fromjson);
        $array = isset($array->frontmatter->tags) ? $array->frontmatter->tags : [];
        $checked = [];
        //var_dump($fromjson, $array);
        if (is_array($array)) {
            foreach ($array as $val) {
                if ($val !== null) {
                    array_push($checked, $val);
                }
            }
        } else {
            $checked = $array;
        }
        return $checked;
    }


    /**
    * @param integer $parentid - id of parentcomment
    *
    * @return array $dropdown - for the form
    */
    public function getDropdown($tags)
    {
        if (is_string($tags)) {
            $dropdown = [
                "type"        => "hidden",
                "value"       => $tags,
            ];
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
                    "heat"   => "värme",
                ],
                "checked"   => $tags,
            ];
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
    public function aForm($id, $sessid, $comm, $comt, $tags)
    {
        $dropdown = $this->getDropdown($tags);

        $this->form->create(
            [
                "id" => "wmd-button-bar",
                "legend" => "Uppdatera ditt konto",
                "wmd" => "wmd-button-bar",
            ],
            [   
                "sessid" => ["type"  => "hidden", "value" => $sessid],
                "id" => ["type"  => "hidden", "value" => $id],
                "userid" => ["type"  => "hidden", "value" => $comm->userid],
                "parentid" => [
                    "type"  => "hidden",
                    "value" => $comm->parentid
                ],
                "title" => [
                    "type" => "text",
                    "label" => "Titel",
                    "validation" => ["not_empty"],
                    "value" => $comm->title,
                    "wrapper-element-class" => "form-group",
                    "class" => "form-control"
                ],
                "comment" => [
                    "type" => "textarea",
                    "label" => "Text",
                    "id" => "wmd-input",
                    "validation" => ["not_empty"],
                    "value" => $comt,
                    "wrapper-element-class" => "form-group",
                    "class" => "form-control wmd-input",
                    "description" => $this->getPlaceholder()
                ],
                "tags" => $dropdown,
                "submit" => ["type" => "submit", "value" => "Spara", "callback" => [$this, "callbackSubmit"]],
            ]
        );
    }



    /**
     * Get details on item to load form with.
     *
     * @param integer $id get details on item with id.
     *
     * @return Comm
     */
    public function getCommDetails($id)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $comm->find("id", $id);
        return $comm;
    }


    /**
    * adds array through frontmatter to $comment
    *
    */
    public function handleTags($tags)
    {
        $elcar = in_array("elcar", $tags) ? "elcar" : null;
        $safety = in_array("safety", $tags) ? "safety" : null;
        $light = in_array("light", $tags) ? "light" : null;
        $heat = in_array("heat", $tags) ? "heat" : null;

        if ($elcar == null && $safety == null && $light == null && $heat == null) {
                $elcar = "elcar";
            }

        $comment->frontmatter['tags'] = [$elcar, $safety, $light, $heat];
    }



    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return boolean true if okey, false if something went wrong.
     */
    public function callbackSubmit()
    {
        $now = date("Y-m-d H:i:s");

        $textfilter = $this->di->get("textfilter");

        $userController = $this->di->get("userController");
        $userdetails = $userController->getOne($this->form->value("sessid"));

        $parses = ["yamlfrontmatter", "shortcode", "markdown", "titlefromheader"];
        $comment = $textfilter->parse($this->form->value("comment"), $parses);
        $comment->frontmatter['title'] = $this->form->value("title");

        $tags = $this->form->value("tags");
        $this->handleTags($tags);

        $comment = json_encode($comment);

        $comm = new Comm();
        $comm->setDb($this->di->get("db"));

        $comm->find("id", $this->form->value("id"));
        $comm->updated = $now;
        $comm->title = $this->form->value("title");
        $comm->userid = $this->form->value("userid");
        $comm->comment = $comment;
        $comm->save();

        $parentid = (int)$this->form->value("parentid");

        $back = $parentid > 0 ? "/view-one/" . $parentid : "/view-one/" . $this->form->value("id");

        $pagerender = $this->di->get("pageRender");
        $pagerender->redirect("comm" . $back);

        return true;
    }
}
