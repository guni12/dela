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
     * @param integer $commentid - question to answer
     *
     * @return string htmlcode for answering a question
     */
    public function getAnswerLink($commentid)
    {
        return '<a href="' . $this->setUrlCreator("comm/create") . '/' . $commentid . '">Svara</a>';
    }



    /**
     * @param integer $commentid - question or answer to comment
     *
     * @return string htmlcode for commenting
     */
    public function getCommentLink($commentid)
    {
        return '<a href="' . $this->setUrlCreator("comm/comment") . '/' . $commentid . '">Kommentera</a>';
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
     * @param array $item - userinfo
     * @param string $viewone - path
     *
     * @return string htmlcode
     */
    public function getUsersHtml($item)
    {
        $gravatar = $this->getGravatar($item['email']);
        $arr['acronym'] = '<a href="' . $this->setUrlCreator("user/view-one") . "/" . $item['id'] . '">' . $item['acronym'] . '</a>';
        $arr['gravatar'] = '<a href="' . $this->setUrlCreator("user/view-one") . "/" . $item['id'] . '">' . $gravatar . '</a>';
        return $arr;
    }


    /**
     * Sort list of dates
     *
     * @param string $first
     * @param string $second - dates
     *
     * @return sorted dates
     */
    public function dateSort($first, $second)
    {
        return strtotime($first->created) - strtotime($second->created);
    }


    /**
    * @param Comm $item - current comment
    * @param array $numbers - counted points, answers and comments
    * @param string $when - when comment was created
    * @param $user
    * @param integer $isadmin
    *
    * @return string $html
    */
    public function getTheText($item, $numbers, $when, $user)
    {
        $gravatar = $this->getGravatar($user[0]);
        $showid = $user[2] === true ? '(' . $item->id . '): ' : "";

        $title = '<a href="' . $this->setUrlCreator("comm/view-one") . '/' . $item->id . '">';
        $title .= $showid . ' ' . $item->title . '</a>';

        $html = '<tr><td class = "allmember">' . $gravatar . ' ' . $user[1] . '</td>';
        $html .= '<td class = "alltitle">' . $title . '</td>';
        $html .= '<td class = "asked">' . $when . '</td>';
        $html .= '<td = "respons"><span class = "smaller">' . $numbers[0] . $numbers[1] . $numbers[2] . '</span></td>';
        $html .= '</tr>';
        return $html;
    }


    /**
     * Returns correct loginlink
     *
     * @param integer $isloggedin
     * @param boolean $isadmin
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