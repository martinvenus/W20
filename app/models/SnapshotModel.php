<?php

/**
 * Data mining system
 * MI-W20 at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2011
 * @package    W20
 * @author     Ján Januška, Jaroslav Líbal, Martin Venuš
 */

/**
 * Class SnapshotModel
 */
class SnapshotModel {
    /*
     * Vybere data týkající se snapshotů do polí
     */

    public static function getSnapshotsAsArray() {
        $result = dibi::query('SELECT id, date, nodes, edges FROM snapshot');

        $times = array();
        $nodes = array();
        $edges = array();

        foreach ($result as $n => $row) {
            array_push($times, $row['date']);
            array_push($nodes, $row['nodes']);
            array_push($edges, $row['edges']);
        }

        unset($result);
        
        $return = array();

        $return['times'] = $times;
        $return['nodes'] = $nodes;
        $return['edges'] = $edges;

        return $return;
    }

    public static function getSnapshots() {
        $result = dibi::query('SELECT id, date, nodes, edges FROM snapshot');

        $all = $result->fetchAll();
        
        return $all;
    }

}

?>
