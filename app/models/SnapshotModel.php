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

    /**
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

    protected static function getGraphs() {

        $result = dibi::query('SELECT id, date, snapshot FROM snapshot');

        $all = $result->fetchAll();

        return $all;
    }

    public static function getDataList() {

        require_once 'Structures/Graph.php';

        $uzly = array();
        $uzly_test = array();
        $hrany = array();

        $vystup = array();

        $lastModified = 0;

        $grafy = SnapshotModel::getGraphs();

        foreach ($grafy as $graf => $g) {

            $g_obj = unserialize($g['snapshot']);

            $graphAllNodes = $g_obj->getNodes();

            foreach ($graphAllNodes as $nodeKey => $nodex) {
                $userInfo = $nodex->getMetadata('userInfo');

                if (!in_array($userInfo['username'], $uzly_test)) {

                    $arcs = $nodex->getNeighbours();
                    for ($i = 0; $i < sizeof($arcs); $i++) {
                        $nData = $arcs[$i]->getMetadata('userInfo');
                        $ui = $nData['username'];
                        $hrana = array("from" => $userInfo['username'], "to" => $ui, "date" => $g['date']);
                        array_push($hrany, $hrana);
                    }
                    $uzel = array();
                    if(isset($userInfo['username'])){
                        $uzel['id'] = $userInfo['username'];
                    }
                    if(isset($userInfo['realname'])){
                        $uzel['realname'] = $userInfo['realname'];
                    }
                    if(isset($userInfo['location'])){
                        $uzel['location'] = $userInfo['location'];
                    }
                    if(isset($userInfo['photosurl'])){
                        $uzel['url'] = $userInfo['photosurl'];
                    }
                    if(isset($g['id'])){
                        $uzel['snapshotId'] = $g['id'];
                    }
                    if(isset($g['date'])){
                        $uzel['date'] = $g['date'];
                    }

                    if ($g['date'] > $lastModified) {
                        $lastModified = $g['date'];
                    }

                    array_push($uzly, $uzel);
                    array_push($uzly_test, $userInfo['username']);
                }
            }
            unset($graphAllNodes);
            unset($g_obj);
        }

        unset($uzly_test);

        array_push($vystup, $uzly);
        array_push($vystup, $hrany);
        array_push($vystup, $lastModified);

        return $vystup;
    }

}

?>
