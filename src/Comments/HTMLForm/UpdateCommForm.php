<?php

namespace Guni\Comments\HTMLForm;

use \Guni\Comments\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\HTMLForm\CreateCommForm;
use \Guni\Comments\Misc;

/**
 * Example of FormModel implementation.
 */
class UpdateCommForm extends FormModel
{
    protected $comm;
    protected $misc;
    protected $createform;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to update
     */
    public function __construct(DIInterface $di, $id=null, $sessid=null)
    {
        parent::__construct($di);
        $this->misc = new Misc($di);
        $this->comm = $this->misc->getItemDetails($id);

        $comt = $this->decode($this->comm->comment);
        $tags = $this->decodeTags($this->comm->comment);

        $this->createform = new CreateCommForm($di, $id);

        $this->aForm($id, $sessid, $comt, $tags);
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
        $toparse = json_decode($fromjson);
        $comt = $textfilter->parse($toparse->text, ["yamlfrontmatter", "shortcode", "markdown", "titlefromheader"]);
        $comt = strip_tags($comt->text);
        return $comt;
    }



    /**
    * @param array $array - tags
    *
    * @return array $checked - with checked tags
    */
    public function fillArrayChecked($array)
    {
        $checked = [];
        foreach ($array as $val) {
            if ($val !== null) {
                array_push($checked, $val);
            }
        }
        return $checked;
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
        $checked = is_array($array) ? $this->fillArrayChecked($array) : $array;
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
     * @param integer $id - comment
     * @param integer $sessid - userid
     * @param string $comt - the text
     * @param array|string $tags
     *
     */
    public function aForm($id, $sessid, $comt, $tags)
    {
        $dropdown = $this->getDropdown($tags);

        $this->form->create(
            [
                "id" => "wmd-button-bar",
                "legend" => "Uppdatera ditt konto",
                "wmd" => "wmd-button-bar",
                "preview" => "wmd-preview",
            ],
            [   
                "sessid" => ["type"  => "hidden", "value" => $sessid],
                "id" => ["type"  => "hidden", "value" => $id],
                "userid" => ["type"  => "hidden", "value" => $this->comm->userid],
                "parentid" => [
                    "type"  => "hidden",
                    "value" => $this->comm->parentid
                ],
                "title" => [
                    "type" => "text",
                    "label" => "Titel",
                    "validation" => ["not_empty"],
                    "value" => $this->comm->title,
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
                    "description" => $this->createform->getPlaceholder()
                ],
                "tags" => $dropdown,
                "submit" => ["type" => "submit", "value" => "Spara", "callback" => [$this, "callbackSubmit"]],
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
        $now = date("Y-m-d H:i:s");

        $textfilter = $this->di->get("textfilter");

        $parses = ["yamlfrontmatter", "shortcode", "markdown", "titlefromheader"];
        $comment = $textfilter->parse($this->form->value("comment"), $parses);
        $comment->frontmatter['title'] = $this->form->value("title");

        $tags = $this->form->value("tags");
        $tags = is_array($tags) ? $tags : [$tags];
        $comment->frontmatter['tags'] = $this->createform->tagsToArray($tags);

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
