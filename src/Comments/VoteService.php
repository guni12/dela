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

        //var_dump($this->comment);
        $session = $this->di->get("session");
        $this->sess = $session->get("user");

        $this->handleVotes($vote);
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


    public function handleVotes($vote)
    {
        $end = $this->comment->id;
        var_dump($end, $vote);
        if ($this->comment->parentid > 0) {
            $end = $this->comment->parentid;
        }
        var_dump($end, $vote);
        $back = "/view-one/" . $end;

        $pagerender = $this->di->get("pageRender");

        $comm = new Comm();
        $comm->setDb($this->di->get("db"));

        if ($vote == "accept") {
            echo "Accept";
            $answerid = $this->comment->id;
            $commentid = $this->getItemDetails($this->comment->parentid);
            if ($commentid->accept == null || $commentid->hasvoted == "null") {
                echo "Null";
                $comm->find("id", $this->comment->parentid);
                $comm->accept = $answerid;
                $comm->save();
                $pagerender->redirect("comm" . $back);
            }
        } else {

            $points = 0 + $this->comment->points;

            if ($this->comment->hasvoted == null || $this->comment->hasvoted == "null") {
                $arr_decoded = [0];
            } else {
                $arr_decoded = json_decode($this->comment->hasvoted);
            }

            $comm->find("id", $this->comment->id);
            if ($vote == "voteup") {
                $comm->points = $points + 1;
            } elseif ($vote == "votedown") {
                $comm->points = $points - 1;
            }

            if ($arr_decoded && in_array($this->sess['id'], $arr_decoded)) {
                $pagerender->redirect("comm" . $back);
            } else {
                array_push($arr_decoded, $this->sess['id']);
                $json = json_encode($arr_decoded);
                $comm->hasvoted = $json;
                //$comm->save();
                //$pagerender->redirect("comm" . $back);
            }
        }
    }
}