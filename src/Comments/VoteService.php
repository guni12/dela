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
     * @return Comm
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
        $commentid = $this->getItemDetails($this->comment->parentid);
        if ($commentid->accept == null || $commentid->hasvoted == "null") {
            $comm->find("id", $this->comment->parentid);
            $comm->accept = $answerid;
            $comm->save();
            $pagerender->redirect("comm" . $back);
        }
    }


    /**
    *
    * @return array $arr_decoded - info of votedcomment
    */
    public function getDecodedArray()
    {
        $arr_decoded = [];
        if (!$this->comment->hasvoted)
        {
            $arr_decoded = [0];
        } else {
            $arr_decoded = json_decode($this->comment->hasvoted);
        }
        return $arr_decoded;
    }


    public function handleVotes($vote)
    {
        $end = $this->comment->id;
        if ($this->comment->parentid > 0) {
            $end = $this->comment->parentid;
        }
        $back = "/view-one/" . $end;
        $pagerender = $this->di->get("pageRender");
        $points = 0 + $this->comment->points;

        $arr_decoded = $this->getDecodedArray();
        $this->comm->find("id", $this->comment->id);
        if ($vote == "voteup") {
            $this->comm->points = $points + 1;
        } elseif ($vote == "votedown") {
            $this->comm->points = $points - 1;
        }

        if ($arr_decoded && in_array($this->sess['id'], $arr_decoded)) {
            $pagerender->redirect("comm" . $back);
        } else {
            array_push($arr_decoded, $this->sess['id']);
            $json = json_encode($arr_decoded);
            $this->comm->hasvoted = $json;
            $this->comm->save();
            $pagerender->redirect("comm" . $back);
        }
    }
}