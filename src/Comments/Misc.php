<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;

/**
 * Helper for html-code
 */
class Misc
{
    protected $di;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
    }

    /**
     * Sets the callable to use for creating routes.
     *
     * @param callable $urlCreate to create framework urls.
     *
     * @return void
     */
    public function setUrlCreator($route)
    {
        $url = $this->di->get("url");
        return call_user_func([$url, "create"], $route);
    }



    /**
     * Get details on all comments.
     *
     * @return Comm
     */
    public function getAll()
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findAll();
    }



    /**
     * Get details on item to load form with.
     *
     * @param integer $id get details on item with id.
     *
     * @return object $comm - actual comment
     */
    public function getItemDetails($id)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        $comm->find("id", $id);
        return $comm;
    }


        /**
     * Get details on item to load form with.
     *
     * @param string $where - sql part for xxx=?
     * @param array $params get details on item with id parentid.
     *
     * @return Comm
     */
    public function findAllWhere($where, $params)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findAllWhere($where, $params);
    }


    /**
     * Get details on item to load form with.
     *
     * @param string $orderby - sql command for column and DESC or ASC
     * @param array $params get details on item with id parentid.
     *
     * @return Comm
     */
    public function getParentOrderDetails($orderby, $params)
    {
        $comm = new Comm();
        $comm->setDb($this->di->get("db"));
        return $comm->findOrderBy("parentid = ?", $orderby, $params);
    }


    /**
     * Returns link for gravatar img
     *
     * @param object $item
     * @return string htmlcode
     */
    public function getGravatar($item, $size = 20)
    {
        $comm = new Comm();
        $gravatar = $comm->getGravatar($item, $size);
        return '<img src="' . $gravatar . '" alt=""/>';
    }


    /**
    * sql command ORDER BY fixed by ActiveRecord
    *
    * @param integer $sort - if answers should be sorted by points
    * @param integer $id - the question for the answers anwers
    */
    public function getAnswers($sort, $id, $comments)
    {
        $orderby = $sort == 1 ? "`points` DESC" : "`created` DESC";
        $params = [$id];
        $comments = $this->getParentOrderDetails($orderby, $params);
        return $comments;
    }


    /**
     * @param integer $commentid - question to answer
     *
     * @return string htmlcode for answering a question
     */
    public function getAnswerLink($commentid)
    {
        $create = $this->setUrlCreator("comm/create");
        return '<a href="' . $create . '/' . $commentid . '">Svara</a>';
    }



    /**
     * @param integer $commentid - question or answer to comment
     *
     * @return string htmlcode for commenting
     */
    public function getCommentLink($commentid)
    {
        $commentpath = $this->setUrlCreator("comm/comment");
        return '<a href="' . $commentpath . '/' . $commentid . '">Kommentera</a>';
    }

    /**
    * @return string htmltext
    */
    public function isUpdated($item)
    {
        if ($item->parentid !== null) {
            return 'Svar: ' . $item->created . ', Ändrad: ' . $item->updated;
        } else {
            return 'Fråga: ' . $item->created . ', Ändrad: ' . $item->updated;
        }
    }


    /**
    * @return string htmltext
    */
    public function isNotUpdated($item)
    {
        if ($item->parentid !== null) {
            return 'Svar: ' . $item->created;
        } else {
            return 'Fråga: ' . $item->created;
        }
    }



    /**
     * Returns when created or updated
     *
     * @param object $item
     * @return string htmlcode
     */
    public function getWhen($item)
    {
        $when = $item->updated ? $this->isUpdated($item) : $this->isNotUpdated($item);
        return $when;
    }


    /**
     * Returns html for user item
     *
     * @param object $item
     * @param string $viewone - path
     *
     * @return string htmlcode
     */
    public function getUsersHtml($item, $viewone)
    {
        $viewone = $this->setUrlCreator("user/view-one") . "/";
        $gravatar = $this->getGravatar($item['email']);
        $arr['acronym'] = '<a href="' . $viewone . $item['id'] . '">' . $item['acronym'] . '</a>';
        $arr['gravatar'] = '<a href="' . $viewone . $item['id'] . '">' . $gravatar . '</a>';
        return $arr;
    }


    /**
     * Sort list of dates
     *
     * @param string $first, $second - dates
     *
     * @return sorted dates
     */
    public function dateSort($first, $second)
    {
        return strtotime($first->created) - strtotime($second->created);
    }


    /**
    * @param obj $item - current comment
    * @param string $viewone - path
    * @param array $numbers - counted points, answers and comments
    * @param string $when - when comment was created
    * @param string $email
    *
    * @return string $html
    */
    public function getTheText($item, $numbers, $when, $user, $isadmin)
    {
        $gravatar = $this->getGravatar($user[0]);
        $showid = $isadmin === true ? '(' . $item->id . '): ' : "";
        $viewone = $this->setUrlCreator("comm/view-one");
        $answers = $numbers[0];
        $comments = $numbers[1];
        $points = $numbers[2];

        $title = '<a href="' . $viewone . '/' . $item->id . '">';
        $title .= $showid . ' ' . $item->title . '</a>';

        $html = '<tr><td class = "allmember">' . $gravatar . ' ' . $user[1] . '</td>';
        $html .= '<td class = "alltitle">' . $title . '</td>';
        $html .= '<td class = "asked">' . $when . '</td>';
        $html .= '<td = "respons"><span class = "smaller">' . $answers . $comments . $points . '</span></td>';
        $html .= '</tr>';
        return $html;
    }


    /**
     * Returns correct loginlink
     *
     * @param string $create
     * @param string $del
     * @param integer $isloggedin
     * @param integer $isadmin
     *
     * @return string htmlcode
     */
    public function getLoginLink($isloggedin, $isadmin)
    {
        $loggedin = '<a href="user/login">Logga in om du vill kommentera</a>';
        if ($isloggedin) {
            $loggedin = ' <a href="' . $this->setUrlCreator("comm/create") .'">Skriv ett inlägg</a>';
            if ($isadmin === true) {
                $loggedin .= ' | <a href="' . $this->setUrlCreator("comm/admindelete") . '">Ta bort ett inlägg</a>';
            }
        }
        return $loggedin;
    }
}