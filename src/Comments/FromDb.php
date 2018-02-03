<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;

/**
 * Helper to fetch objects
 */
class FromDb
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
     * @return Comm $comm - actual comment
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
     * @param array|integer|string $params get details on item with id parentid.
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
    * sql command ORDER BY fixed by ActiveRecord
    *
    * @param integer $sort - if answers should be sorted by points
    * @param integer $id - the question for the answers anwers
    */
    public function getAnswers($sort, $id)
    {
        $orderby = $sort == 1 ? "`points` DESC" : "`created` DESC";
        $params = [$id];
        $comments = $this->getParentOrderDetails($orderby, $params);
        return $comments;
    }
}