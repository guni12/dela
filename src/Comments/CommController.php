<?php

namespace Guni\Comments;

use \Anax\Configure\ConfigureInterface;
use \Anax\Configure\ConfigureTrait;
use \Anax\DI\InjectionAwareInterface;
use \Anax\DI\InjectionAwareTrait;
use \Guni\Comments\HTMLForm\CreateCommForm;
use \Guni\Comments\HTMLForm\UpdateCommForm;
use \Guni\Comments\HTMLForm\DeleteCommForm;
use \Guni\Comments\IndexPage;
use \Guni\Comments\ShowOneService;
use \Guni\Comments\ShowAllService;
use \Guni\Comments\Taglinks;
use \Guni\Comments\VoteService;
use \Guni\Comments\FromDb;
use \Guni\User\UserHelp;

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
        return $this->di->get("session")->get("user");
    }


    /**
     * Show all items.
     *
     * @return void
     */
    public function getIndex()
    {
        $text = new ShowAllService($this->di);
        $data = [
            "items" => $text->getHTML(),
        ];
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Frågor", "comm/crud/front", $data);
    }


    public function getIndexPage()
    {
        $text = new IndexPage($this->di);
        $arr = $text->getHTML();
        $data = ['items' => $arr];
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("BitOfAll", "comm/crud/index", $data);
    }

    /**
     * Handler with form to create a new item.
     *
     * @return void
     */
    public function getPostCreateItem($id = null)
    {
        $sess = $this->getSess();

        if ($sess) {
            $form = new CreateCommForm($this->di, null, $sess['userid'], null);
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
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Skriv en fråga", "comm/crud/create", $data);
    }

    /**
     * Handler with form to create a new item.
     *
     * @return void
     */
    public function getPostCommentItem($id = null)
    {
        $sess = $this->getSess();

        if ($sess) {
            $form       = new CreateCommForm($this->di, 1, $sess['id'], $id);
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
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Skriv din kommentar", "comm/crud/create", $data);
    }


    /**
     * Handler with form to delete an item.
     *
     * @return void
     */
    public function getPostDeleteItem($id)
    {
        $sess = $this->getSess();
        $fromdb = new FromDb($this->di);
        $comm = $fromdb->getItemDetails($id);

        if ($sess && $sess['id'] == $comm->userid || $sess['isadmin'] == 1) {
            $form = new DeleteCommForm($this->di, $id);
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
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Ta bort ett inlägg", "comm/crud/delete", $data);
    }



    /**
     * Handler with form to update an item.
     *
     * @return void
     */
    public function getPostUpdateItem($id)
    {
        $sess = $this->getSess();
        $fromdb = new FromDb($this->di);
        $comm = $fromdb->getItemDetails($id);

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
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Uppdatera ditt inlägg", "comm/crud/update", $data);
    }


    /**
     * Handler with form to just show an item.
     *
     * @return void
     */
    public function getPostShow($id)
    {
        $text       = new ShowOneService($this->di, $id);
        $data = [
            "items" => $text->getHTML(),
        ];
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Inlägg", "comm/crud/view-one", $data);
    }


    /**
     * Handler with form to just show an item.
     *
     * @return void
     */
    public function getPostShowPoints($id)
    {
        $text       = new ShowOneService($this->di, $id, 1);
        $data = [
            "items" => $text->getHTML(),
        ];
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Inlägg-poängsort", "comm/crud/view-one", $data);
    }


    public function getTagList()
    {
        $text = new Taglinks($this->di);
        $arr = $text->getHTML();
        $data = ['items' => $arr];
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Taggar", "comm/crud/view-one", $data);
    }

    public function getTagsShow($id)
    {
        $text = new ShowTagsService($this->di, $id);
        $arr = $text->getHTML();
        $data = ['items' => $arr];
        $userhelp = new UserHelp($this->di);
        $userhelp->toRender("Taggar", "comm/crud/view-one", $data);
    }


    public function makeVoteUp($id)
    {
        new VoteService($this->di, $id, "voteup");
    }


    public function makeVoteDown($id)
    {
        new VoteService($this->di, $id, "votedown");
    }

    public function makeAccept($id)
    {
        new VoteService($this->di, $id, "accept");
    }
}
