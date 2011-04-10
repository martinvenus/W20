<?php

/**
 * Data mining system
 * MI-W20 at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2011
 * @package    W20
 * @author     Ján Januška, Jaroslav Líbal, Martin Venuš
 */
use Nette\Object, \Nette\String;

;

/**
 * Flickr Analyse Model.
 *
 * @package    W20
 */
class FlickrAnalyseModel extends Object {

    /**
     * ID uživatele ve struktuře
     */
    protected $userId;

    /**
     * Konstruktor objektu
     */
    public function FlickrAnalyseModel() {
        $this->userId = 0;
    }

    /**
     * Metoda, která analyzuje klíčová slova a vkládá autory fotografií do grafu
     * Vytváří hrany mezi jednotlivými autory
     *
     * @param FlickrModel $flickr objekt flickr API
     * @param Structures_Graph $graph objekt grafové struktury
     * @param String $keyword klíčové slovo
     */
    public function analyseKeyword($flickr = null, $graph = null, $keyword) {
        $keywordStored = String::webalize($keyword);

        $photos = $flickr->photos_search(array("tags" => $keyword, "tag_mode" => "all", "sort" => "interestingness-desc", "media" => "photos", "per_page" => 50));

        /* Projdeme všechny fotografie */
        foreach ($photos['photo'] as $key => $photo) {
            $addNewNode = true;

            $graphAllNodes = $graph->getNodes();

            /* Zjistíme, zda již uživatel existuje v grafu */
            /* V případě, že existuje, zaevidujeme u něj pouze nové klíčové slovo */
            foreach ($graphAllNodes as $nodeKey => $nodex) {
                if (strcmp($nodex->getMetadata('username'), $photo['owner']) == 0) {
                    $nodex->setMetadata($keywordStored, 1);
                    $addNewNode = false;
                    unset($nodex);
                    break;
                }

                unset($nodex);
            }
            unset($graphAllNodes);

            /* V případě, že uživatel neexistuje, vložíme ho */
            if ($addNewNode == true) {
                $node = new Structures_Graph_Node();
                $node->setMetadata('id', $this->userId);
                $node->setMetadata('username', $photo['owner']);
                $node->setMetadata($keywordStored, 1);
                $graph->addNode($node);
                $this->userId++;

                unset($node);
            }
        }

        $graphAllNodes = $graph->getNodes();

        /* Vytvoříme hrany mezi všemi uživateli v daném klíčovém slově */
        foreach ($graphAllNodes as $nodeKey => $nodex) {
            foreach ($graphAllNodes as $nodeKeyInt => $nodeInt) {
                if ($nodex->metadataKeyExists($keywordStored) && $nodeInt->metadataKeyExists($keywordStored)) {
                    $keyword1 = (int) $nodex->getMetadata($keywordStored);
                    $keyword2 = (int) $nodeInt->getMetadata($keywordStored);

                    if (($keyword1 == 1) && ($keyword2 == 1)) {
                        if ($nodeKeyInt >= $nodeKey) {
                            if ($nodeKey != $nodeKeyInt) {
                                $nodex->connectTo($nodeInt);
                                //echo "Spojuji vrchol " . $nodex->getMetadata('id') . " a vrchol " . $nodeInt->getMetadata('id') . "<br />";
                                //echo "Vrchol " . $nodex->getData() . " ma stupen " . $nodex->inDegree() . "<br />";
                            }
                        }
                    }
                }
                unset($nodeInt);
            }
            unset($nodex);
        }

        unset($graphAllNodes);
    }

    /**
     * Metoda, získá všechna klíčová slova
     *
     * @return dataSource
     */
    public static function getKeywords() {
        $result = dibi::dataSource('SELECT * FROM keyword');

        return $result;
    }

    /**
     * Metoda vloží informace o získaném snapshotu do databáze
     *
     * @param int $numberOfNodes počet vrcholů
     * @param int $numberOfEdges počet hran
     * @param serialized object $graph serializovaný objekt grafu (uloží se do datového typu BLOB)
     */
    public static function addSnapshot($numberOfNodes, $numberOfEdges, $graph) {
        dibi::query('INSERT INTO snapshot (nodes, edges, date, snapshot) VALUES (%i, %i, %i, %s)', $numberOfNodes, $numberOfEdges, time(), $graph);
    }

    /**
     * Metoda, která přidá informace o uivateli ke každému vrcholu
     *
     * @param Structured_Graph $graph
     * @param FlickrModel $flickr
     */
    public static function addInfoToUsers($graph, $flickr) {
        $graphAllNodes = $graph->getNodes();

        foreach ($graphAllNodes as $nodeKey => $node) {

            $userInfo = $flickr->people_getInfo($node->getMetadata('username'));

            $node->setMetadata('userInfo', $userInfo);

            unset($node);
        }

        unset($graphAllNodes);
    }

}