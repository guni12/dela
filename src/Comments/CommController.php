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
        //$tags = $comm->
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

            $data = [
                "form" => $form->getHTML(),
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

            $data = [
                "form" => $form->getHTML(),
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
     * Handler with form to delete an item.
     *
     * @return void
     */
    public function getPostDeleteItem($id)
    {
        $title      = "Ta bort ett inlägg";
        $sess = $this->getSess();
        echo $id;

        $comm = $this->findOne($id);

        if ($sess && $sess['id'] == $comm->userid) {
            $form       = new DeleteCommForm($this->di, $id);
            $form->check();

            $data = [
                "form" => $form->getHTML(),
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

            $data = [
                "form" => $form->getHTML(),
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

        $comm = $this->findOne($id);

        if ($sess && $sess['id'] == $comm->userid || $sess['isadmin'] == 1) {
            $form       = new UpdateCommForm($this->di, $id, $sess['id']);
            $form->check();

            $data = [
                "form" => $form->getHTML(),
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
        $sess = $this->getSess();

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
        $sess = $this->getSess();

        $text       = new ShowOneService($this->di, $id, $sort);

        $data = [
            "items" => $text->getHTML(),
        ];

        $crud = "comm/crud/view-one";
        $this->toRender($title, $crud, $data);
    }

    /**
    *
    *
    */
    public function getGravatar($email, $size = 20)
    {
        $dim = 'mm';
        $rad = 'g';
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$size&d=$dim&r=$rad";
        return $url;
    }


    /**
    * @param string id to check
    * @return idobject
    */
    public function findOne($id)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $one = $comm->find("id", $id);
        return $one;
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
        //$tags = $comm->
        $crud = "comm/crud/view-one";
        $this->toRender($title, $crud, $data);
    }


    public function makeVoteUp($id)
    {
        $up = "voteup";
        $text = new VoteService($this->di, $id, $up);
    }


    public function makeVoteDown($id)
    {
        $down = "votedown";
        $text = new VoteService($this->di, $id, $down);
    }

    public function makeAccept($id)
    {
        $accept = "accept";
        $text = new VoteService($this->di, $id, $accept);
    }
}
