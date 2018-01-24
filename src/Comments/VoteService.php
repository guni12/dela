<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;

/**
 * Helper to handle rating and points
 */
class VoteService
{
    /**
    * @var array $comment, the chosen comment.
    */
    protected $comment;
    protected $sess;
    protected $comm;
    protected $di;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to show
     */
    public function __construct(DIInterface $di, $id, $vote)
    {
        $this->di = $di;
        $this->comment = $this->getItemDetails($id);

        $session = $this->di->get("session");
        $this->sess = $session->get("user");

        $this->comm = new Comm();
        $this->comm->setDb($this->di->get("db"));

        if ($vote == "accept") {
            $this->acceptVote($this->comm);
        } else {
            $this->handleVotes($vote);
        }
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
    *
    * @param obj $comm - commentobject
    * saves which comment is accepted to db
    */
    public function acceptVote($comm)
    {
        $answerid = $this->comment->id;
        $pagerender = $this->di->get("pageRender");
        $commentid = $this->getItemDetails($this->comment->parentid);
        if ($commentid->accept == null || $commentid->hasvoted == "null") {
            $comm->find("id", $this->comment->parentid);
            $comm->accept = $answerid;
            $comm->save();
            $pagerender->redirect("comm/view-one/" . $answerid);
        }
    }


    /**
    *
    * @return array $arr_decoded - info of votedcomment
    */
    public function getDecodedArray()
    {
        if (!$this->comment->hasvoted)
        {
            return [0];
        } else {
            return json_decode($this->comment->hasvoted);
        }
    }



    /**
    * Saves updated vote to db
    * @param string $arr_decoded - decoded votecontent
    * @param obj $pagerender -connection to class
    */
    public function saveVote($arr_decoded, $pagerender, $back)
    {
        array_push($arr_decoded, $this->sess['id']);
        $json = json_encode($arr_decoded);
        $this->comm->hasvoted = $json;
        $this->comm->save();
        $pagerender->redirect("comm" . $back);
    }



    /**
    * Updates votes for the comment
    * @param string $vote - votecontent
    */
    public function handleVotes($vote)
    {
        $end = $this->comment->parentid > 0 ? $this->comment->parentid : $this->comment->id;

        $back = "/view-one/" . $end;
        $pagerender = $this->di->get("pageRender");
        $points = 0 + $this->comment->points;

        $arr_decoded = $this->getDecodedArray();
        $this->comm->find("id", $this->comment->id);
        $this->comm->points = $vote == "voteup" ? $points + 1 : $vote == "votedown" ? $points - 1 : $this->comm->points;

        ($arr_decoded && in_array($this->sess['id'], $arr_decoded)) ? $pagerender->redirect("comm" . $back) : $this->saveVote($arr_decoded, $pagerender, $back);
    }
}