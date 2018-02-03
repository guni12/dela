<?php

namespace Guni\User;

use \Anax\DI\DIInterface;
use \Guni\User\User;
use \Guni\Comments\Comm;
use \Guni\Comments\Misc;

/**
 * Form to update an item.
 */
class UserHelp
{
    /**
    * @var array $comments, all comments.
    */
    protected $di;
    protected $comm;


    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;

        $this->comm = new Comm();
    }

    /**
     * Get details on comments.
     *
     *
     * @return All comments
     */
    public function getAll()
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        return $user->findAll();
    }


    /**
     * Get details on comments.
     *
     * @param integer $id - user
     * @return object user with id
     */
    public function getUserItems($id)
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        return $user->find("id", $id);
    }


    /**
     * @param integer $comm - boolean if item is comment or not
     * @param string $parent - of parentnumber
     *
     * @return string - where parent is id and not a comment
     */
    public function getIsAnswer($parent, $comm)
    {
        if ($comm > 0) {
            return null;
        } else {
            return $parent;
        }
    }



    /**
     * @param integer $comm - boolean if item is comment or not
     * @param string $parent - of parentnumber
     *
     * @return string - where parent is id and is a comment
     */
    public function getIsComment($parent, $comm)
    {
        if ($comm > 0) {
            return $parent;
        } else {
            return null;
        }
    }


    /**
     * @param string $eng - the tag name in english
     *
     * @return string - tag name in swedish
     */
    public function getName($eng)
    {
        switch ($eng) {
            case "elcar":
                return "Elbil";
                break;
            case "safety":
                return "Säkerhet";
                break;
            case "light":
                return "Belysning";
                break;
            case "heat":
                return "Värme";
        }
    }


    /**
    * @param integer $id - userid
    * @return integer points nr of comments
    */
    public function getComPoints($id)
    {
        $this->comm->setDb($this->di->get("db"));
        $commentsql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE userid = ' . $id . ' AND iscomment = 1 AND parentid IS NOT NULL';

        return $this->comm->findSql($commentsql)[0]->count ? $this->comm->findSql($commentsql)[0]->count : 0;
    }


    /**
    * @param integer $id - userid
    * @return integer points nr of asked questions
    */
    public function getQuesPoints($id)
    {
        $this->comm->setDb($this->di->get("db"));
        $questionssql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE userid = ' . $id . ' AND parentid IS NULL';

        return $this->comm->findSql($questionssql)[0]->count ? $this->comm->findSql($questionssql)[0]->count : 0;
    }


    /**
    * @param integer $id - userid
    * @return integer points nr of answers
    */
    public function getAnsPoints($id)
    {
        $this->comm->setDb($this->di->get("db"));
        $answersql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE userid = ' . $id . ' AND iscomment = 0 AND parentid IS NOT NULL OR userid = ' . $id . ' AND iscomment IS NULL AND parentid IS NOT NULL';

        return $this->comm->findSql($answersql)[0]->count ? $this->comm->findSql($answersql)[0]->count : 0;        
    }


    /**
     * @param integer $reputation - points summed up
     * @param integer $id - userid
     *
     * @return string - html-text for the points
     */
    public function getPointsHTML($reputation, $id)
    {
        $this->comm->setDb($this->di->get("db"));
        $votedsql = 'SELECT *,COUNT(*) as count FROM `comm` WHERE hasvoted LIKE "%' . $id . '%"';

        $votedcount = $this->comm->findSql($votedsql)[0]->count;

        $pointssql = 'SELECT SUM(`points`) AS count FROM `comm` WHERE userid = ' . $id;

        $pointcount = $this->comm->findSql($pointssql)[0]->count ? $this->comm->findSql($pointssql)[0]->count : 0;

        return '<p>Rykte: ' . $reputation . ', Fått röster: ' . $pointcount . ', ' . 'Har röstat: ' . $votedcount . '</p>';
    }



    /**
    *
    *
    */
    public function getAllUsers()
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        $users = $user->findAll();
        return $users;
    }


    /**
    *
    * @return array userinfo of $id if exist
    */
    public function getOne($id)
    {
        $user = new User();
        $user->setDb($this->di->get("db"));
        $one = $user->find("id", $id);

        if ($one) {
            $data = [
                "email" => $one->email,
                "id" => $one->id,
                "acronym" => $one->acronym,
                "profile" => $one->profile,
                "created" => $one->created,
                "isadmin" => $one->isadmin,
            ];
        } else {
            $data = [];
        }

        return $data;
    }


    /**
     * Get all items as array suitable for display in select option dropdown.
     *
     * @return array with key value of all items.
     */
    public function getSelectUsers()
    {
        $users = ["-1" => "Välj en användare..."];
        foreach ($this->getAll() as $obj) {
            $users[$obj->id] = "{$obj->acronym} ({$obj->id})";
        }

        return $users;
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
}