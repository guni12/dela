<?php

namespace Guni\Comments;

use \Anax\Configure\ConfigureInterface;
use \Anax\Configure\ConfigureTrait;
use \Anax\DI\InjectionAwareInterface;
use \Anax\DI\InjectionAwareTrait;
use \Guni\Comments\HTMLForm\CreateCommForm;
use \Guni\Comments\HTMLForm\UpdateCommForm;
use \Guni\Comments\HTMLForm\DeleteCommForm;
use \Guni\Comments\HTMLForm\AdminDeleteCommForm;
use \Guni\Comments\IndexPage;
use \Guni\Comments\ShowOneService;
use \Guni\Comments\ShowAllService;
use \Guni\Comments\Taglinks;
use \Guni\Comments\VoteService;
use \Guni\Comments\Misc;

/**
 * A controller class.
 */
class CommController implements
    ConfigureInterface,
    InjectionAwareInterface
{
    use ConfigureTrait,
        InjectionAwareTrait;


    /**
    *
    * @return sessionobject
    */
    public function getSess()
    {
        $session = $this->di->get("session");
        $sess = $session->get("user");
        return $sess;
    }



    /**
     * Sends data to view
     *
     * @param string $title
     * @param string $crud, path to view
     * @param array $data, htmlcontent to view
     */
    public function toRender($title, $crud, $data)
    {
        $view       = $this->di->get("view");
        $pageRender = $this->di->get("pageRender");
        $view->add($crud, $data);
        $tempfix = "";
        $pageRender->renderPage($tempfix, ["title" => $title]);
    }


    /**
     * Show all items.
     *
     * @return void
     */
    public function getIndex()
    {
        $title      = "Frågor";

        $text = new ShowAllService($this->di);

        $data = [
            "items" => $text->getHTML(),
        ];

        $crud = "comm/crud/front";
        $this->toRender($title, $crud, $data);
    }


    public function getIndexPage()
    {
        $title = "BitOfAll";

        $text = new IndexPage($this->di);
        $arr = $text->getHTML();
        $data = ['items' => $arr];
        $crud = "comm/crud/index";
        $this->toRender($title, $crud, $data);
    }

    /**
     * Handler with form to create a new item.
     *
     * @return void
     */
    public function getPostCreateItem($id = null)
    {
        $title      = "Skriv en fråga";
        $iscomment = null;
        $parentid = null;

        $sess = $this->getSess();

        if ($sess) {
            $form       = new CreateCommForm($this->di, $iscomment, $sess['id'], $id, $parentid);
            $form->check();

            $text = '<div class="col-lg-12 col-sm-12 col-xs-12">';
            $text .= $form->getHTML() . '</div>';

            $data = [
                "form" => $text,
            ];
        } else {
            $data = [
                "form" => "Enbart för inloggade. Sorry!",
            ];
        }

        $crud = "comm/crud/create";
        $this->toRender($title, $crud, $data);
    }

    /**
     * Handler with form to create a new item.
     *
     * @return void
     */
    public function getPostCommentItem($id = null)
    {
        $title      = "Skriv din kommentar";
        $iscomment = 1;

        $sess = $this->getSess();

        if ($sess) {
            $form       = new CreateCommForm($this->di, $iscomment, $sess['id'], $id);
            $form->check();

            $text = '<div class="col-lg-12 col-sm-12 col-xs-12">';
            $text .= $form->getHTML() . '</div>';

            $data = [
                "form" => $text,
            ];
        } else {
            $data = [
                "form" => "Enbart för inloggade. Sorry!",
            ];
        }

        $crud = "comm/crud/create";
        $this->toRender($title, $crud, $data);
    }


    /**
     * Handler with form to delete an item.
     *
     * @return void
     */
    public function getPostDeleteItem($id)
    {
        $title      = "Ta bort ett inlägg";
        $sess = $this->getSess();

        $misc = new Misc($this->di);
        $comm = $this->getItemDetails($id);

        if ($sess && $sess['id'] == $comm->userid) {
            $form       = new DeleteCommForm($this->di, $id);
            $form->check();

            $text = '<div class="col-lg-12 col-sm-12 col-xs-12">';
            $text .= $form->getHTML() . '</div>';

            $data = [
                "form" => $text,
            ];
        } else {
            $data = [
                "form" => "Inte ditt id. Sorry!",
            ];
        }

        $crud = "comm/crud/delete";
        $this->toRender($title, $crud, $data);
    }



    /**
     * Handler with form to update an item.
     *
     * @return void
     */
    public function getPostAdminDeleteItem()
    {
        $title      = "Ta bort text";
        $sess = $this->getSess();

        if ($sess['isadmin'] == 1) {
            $form       = new AdminDeleteCommForm($this->di);

            $form->check();
            $text = '<div class="col-lg-12 col-sm-12 col-xs-12">';
            $text .= $form->getHTML() . '</div>';

            $data = [
                "form" => $text,
            ];
        } else {
            $data = [
                "form" => "Enbart för admin. Sorry!",
            ];
        }

        $crud = "comm/crud/admindelete";
        $this->toRender($title, $crud, $data);
    }



    /**
     * Handler with form to update an item.
     *
     * @return void
     */
    public function getPostUpdateItem($id)
    {
        $title      = "Uppdatera ditt inlägg";
        $sess = $this->getSess();

        $misc = new Misc($this->di);
        $comm = $misc->getItemDetails($id);

        if ($sess && $sess['id'] == $comm->userid || $sess['isadmin'] == 1) {
            $form       = new UpdateCommForm($this->di, $id, $sess['id']);
            $form->check();

            $text = '<div class="col-lg-12 col-sm-12 col-xs-12">';
            $text .= $form->getHTML() . '</div>';

            $data = [
                "form" => $text,
            ];
        } else {
            $data = [
                "form" => "Inte ditt id. Sorry!",
            ];
        }

        $crud = "comm/crud/update";
        $this->toRender($title, $crud, $data);
    }


    /**
     * Handler with form to just show an item.
     *
     * @return void
     */
    public function getPostShow($id)
    {
        $title      = "Inlägg";
        $text       = new ShowOneService($this->di, $id);

        $data = [
            "items" => $text->getHTML(),
        ];

        $crud = "comm/crud/view-one";
        $this->toRender($title, $crud, $data);
    }


    /**
     * Handler with form to just show an item.
     *
     * @return void
     */
    public function getPostShowPoints($id)
    {
        $title      = "Inlägg-poängsort";
        $sort = 1;

        $text       = new ShowOneService($this->di, $id, $sort);

        $data = [
            "items" => $text->getHTML(),
        ];

        $crud = "comm/crud/view-one";
        $this->toRender($title, $crud, $data);
    }


    public function getTagList()
    {
        $title = "Taggar";

        $text = new Taglinks($this->di);
        $arr = $text->getHTML();
        $data = ['items' => $arr];
        $crud = "comm/crud/view-one";
        $this->toRender($title, $crud, $data);
    }

    public function getTagsShow($id)
    {
        $title = "Taggar";

        $text = new ShowTagsService($this->di, $id);
        $arr = $text->getHTML();
        $data = ['items' => $arr];
        $crud = "comm/crud/view-one";
        $this->toRender($title, $crud, $data);
    }


    public function makeVoteUp($id)
    {
        $voteup = "voteup";
        new VoteService($this->di, $id, $voteup);
    }


    public function makeVoteDown($id)
    {
        $down = "votedown";
        new VoteService($this->di, $id, $down);
    }

    public function makeAccept($id)
    {
        $accept = "accept";
        new VoteService($this->di, $id, $accept);
    }
}
