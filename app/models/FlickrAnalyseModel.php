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

    public static function analyseKeyword($flickr = null, $graph = null, $keyword) {
        $keywordStored = String::webalize($keyword);

        $photos = $flickr->photos_search(array("tags" => $keyword, "tag_mode" => "any", "sort" => "interestingness-desc", "media" => "photos", "per_page" => 500));

        $i = 0;
        foreach ($photos['photo'] as $key => $photo) {
            $addNewNode = true;
            //$user = $f->people_getInfo($photo['owner']);

            $graphAllNodes = $graph->getNodes();

            foreach ($graphAllNodes as $nodeKey => $nodex) {
                if (strcmp($nodex->getMetadata('username'), $photo['owner']) == 0) {
                    $nodex->setMetadata($keywordStored, 1);
                    $addNewNode = false;
                    break;
                }

                unset($nodex);
            }

            if ($addNewNode == true) {
                $node = new Structures_Graph_Node();
                $node->setMetadata('id', $i);
                $node->setMetadata('username', $photo['owner']);
                $node->setMetadata($keywordStored, 1);
                $graph->addNode($node);
                $i++;

                unset($node);
            }
        }

        $graphAllNodes = $graph->getNodes();

        foreach ($graphAllNodes as $nodeKey => $nodex) {
            foreach ($graphAllNodes as $nodeKeyInt => $nodeInt) {
                if (@((int) $nodex->getMetadata($keywordStored) == 1) && (@(int) $nodeInt->getMetadata($keywordStored) == 1))
                    if ($nodeKeyInt >= $nodeKey) {
                        if ($nodeKey != $nodeKeyInt) {
                            $nodex->connectTo($nodeInt);
//                        echo "Spojuji vrchol " . $nodex->getMetadata('id') . " a vrchol " . $nodeInt->getMetadata('id') . "<br />";
//                        echo "Vrchol " . $nodex->getData() . " ma stupen " . $nodex->inDegree() . "<br />";

                            unset($nodeInt);
                        }
                    }
            }

            unset($nodex);
        }
    }

    public static function getKeywords() {
        $result = dibi::dataSource('SELECT * FROM keyword');

        return $result;
    }

    public static function addSnapshot($numberOfNodes, $numberOfEdges, $graph) {
        dibi::query('INSERT INTO snapshot (nodes, edges, date, snapshot) VALUES (%i, %i, %i, %s)', $numberOfNodes, $numberOfEdges, time(), $graph);
    }

}