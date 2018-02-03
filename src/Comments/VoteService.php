<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\FromDb;

/**
 * Helper to handle rating and points
 */
class VoteService
{
    /**
    * @var object $comment, the chosen comment.
    */
    protected $comment;
    protected $sess;
    protected $comm;
    protected $di;
    protected $fromdb;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     * @param integer             $id to show
     */
    public function __construct(DIInterface $di, $id, $vote)
    {
        $this->di = $di;
        $this->fromdb = new FromDb($di);
        $this->comment = $this->fromdb->getItemDetails($id);

        $session = $this->di->get("session");
        $this->sess = $session->get("user");

        $this->comm = new Comm();
        $this->comm->setDb($this->di->get("db"));

        $vote == "accept" ? $this->acceptVote() : $this->handleVotes($vote);
    }


    /**
    *
    * saves which comment is accepted to db
    */
    public function acceptVote()
    {
        $answerid = $this->comment->id;
        $pagerender = $this->di->get("pageRender");
        $commentid = $this->fromdb->getItemDetails($this->comment->parentid);
        if ($commentid->accept == null || $commentid->hasvoted == "null") {
            $this->comm->find("id", $this->comment->parentid);
            $this->comm->accept = $answerid;
            $this->comm->save();
            $pagerender->redirect("comm/view-one/" . $commentid);
        }
    }


    /**
    *
    * @return array $arrDecoded - info of votedcomment
    */
    public function getDecodedArray()
    {
        if (!$this->comment->hasvoted) {
            return [0];
        } else {
            return json_decode($this->comment->hasvoted);
        }
    }



    /**
    * Saves updated vote to db
    * @param array $arrDecoded - decoded votecontent
    * @param obj $pagerender -connection to class
    */
    public function saveVote($arrDecoded, $pagerender, $back)
    {
        array_push($arrDecoded, $this->sess['id']);
        $json = json_encode($arrDecoded);
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

        $arrDecoded = $this->getDecodedArray();
        $this->comm->find("id", $this->comment->id);
        $this->comm->points = $vote == "voteup" ? $points + 1 : ($vote == "votedown" ? $points - 1 : $this->comm->points);

        ($arrDecoded && in_array($this->sess['id'], $arrDecoded)) ? $pagerender->redirect("comm" . $back) : $this->saveVote($arrDecoded, $pagerender, $back);
    }
}
