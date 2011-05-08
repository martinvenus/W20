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


    /**
     * Vybere snapshoty ke zpracování
     */
    protected static function getGraphs() {

        $result = dibi::query('SELECT id, date, snapshot FROM snapshot');

        $all = $result->fetchAll();

        return $all;
    }

    /**
     * Vrátí jeden snapshot
     */
    public static function getGraph($snap) {

        $result = dibi::query('SELECT snapshot FROM snapshot WHERE id=%i', $snap);

        $single = $result->fetchSingle();
        
        return $single;
    }

    /**
     * Zpracuje snapshoty a vrátí oddělené uzly a hrany přeš všechny snapshoty
     */
    public static function getDataList() {

        // include struktury grafu (nutné pro unserialize)
        require_once 'Structures/Graph.php';

        // dílčí výstupní pole a pole pro kontrolu zda již hodnota daného vrcholu nebyla uložena
        $uzly = array();
        $uzly_test = array();
        $hrany = array();

        // závěrečné pole které se vrací z metody
        $vystup = array();

        // datum poslední změny
        $lastModified = 0;

        // získáme všechny snapshoty z databáze
        $grafy = SnapshotModel::getGraphs();

        // iterace přes všechny snapshoty
        foreach ($grafy as $graf => $g) {

            // získáme objekt grafu
            $g_obj = unserialize($g['snapshot']);

            // všechny uzly grafu
            $graphAllNodes = $g_obj->getNodes();

            foreach ($graphAllNodes as $nodeKey => $nodex) {

                // informace o uživateli (uzlu)
                $userInfo = $nodex->getMetadata('userInfo');

                // pokud ještě tento uzel neznáme
                if (!in_array($userInfo['username'], $uzly_test)) {

                    // vezmeme všechny jeho sousedy (hrany vedoucí z uzlu) a uložíme je mezi hrany
                    $arcs = $nodex->getNeighbours();
                    for ($i = 0; $i < sizeof($arcs); $i++) {
                        $nData = $arcs[$i]->getMetadata('userInfo');
                        $ui = $nData['username'];
                        // ukládáme odkud kam hrana vede a datum kdy byla objevena
                        $hrana = array("from" => $userInfo['username'], "to" => $ui, "date" => $g['date']);
                        array_push($hrany, $hrana);
                    }

                    // uložíme si nový uzel, ukládáme uživatelské jméno, reálné jméno, bydliště, ulr photostreamu, id snapshotu a datum přidání
                    $uzel = array();
                    if (isset($userInfo['username'])) {
                        $uzel['id'] = $userInfo['username'];
                    }
                    if (isset($userInfo['realname'])) {
                        $uzel['realname'] = $userInfo['realname'];
                    }
                    if (isset($userInfo['location'])) {
                        $uzel['location'] = $userInfo['location'];
                    }
                    if (isset($userInfo['photosurl'])) {
                        $uzel['url'] = $userInfo['photosurl'];
                    }
                    if (isset($g['id'])) {
                        $uzel['snapshotId'] = $g['id'];
                    }
                    if (isset($g['date'])) {
                        $uzel['date'] = $g['date'];
                    }

                    // pokud je novější časový údaj poslední změny
                    if ($g['date'] > $lastModified) {
                        $lastModified = $g['date'];
                    }

                    // přidáme uzel mezi uzly
                    array_push($uzly, $uzel);
                    array_push($uzly_test, $userInfo['username']);
                }
            }
            // nepotřebné proměnné uvolníme
            unset($graphAllNodes);
            unset($g_obj);
        }

        // nepotřebné proměnné uvolníme
        unset($uzly_test);

        // spojíme pole do jednoho výstupního
        array_push($vystup, $uzly);
        array_push($vystup, $hrany);
        array_push($vystup, $lastModified);

        return $vystup;
    }

}

?>
