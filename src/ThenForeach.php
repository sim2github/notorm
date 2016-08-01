<?php
namespace NotORM;

/**
 * NotORM_Result::thenForeach() helper
 */
class ThenForeach {
    protected $callback;

    /** Create callback
     * @param $callback
     */
    function __construct($callback) {
        $this->callback = $callback;
    }

    /** Call callback for each row
     * @param \NotORM\Result
     * @return null
     */
    function __invoke(Result $result) {
        $callback = $this->callback;
        foreach ($result as $id => $row) {
            $callback($row, $id);
        }
    }

}