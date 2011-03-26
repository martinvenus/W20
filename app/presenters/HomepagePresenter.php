<?php

/**
 * Data mining system
 * MI-W20 at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2011
 * @package    W20
 * @author     Ján Januška, Jaroslav Líbal, Martin Venuš
 */
use Nette\Debug;

/**
 * Homepage presenter.
 *
 * @author     Martin Venuš
 * @package    W20
 */
class HomepagePresenter extends BasePresenter {

    public function renderDefault() {
        
    }

    /**
     * Metoda, která získá obrázky z Flickr API na klíčová slova získaná z databáze
     */
    public function actionFlickr() {
        require_once 'Structures/Graph.php';

        $flickr = new FlickrModel("eb01c6c4e23a0f036988692a7f42dd14");

        $graph = new Structures_Graph(false);

        $keywords = FlickrAnalyseModel::getKeywords();
        $keywords = $keywords->fetchPairs('id', 'keyword');

        foreach ($keywords as $keyword) {
            FlickrAnalyseModel::analyseKeyword($flickr, $graph, $keyword);
        }

        $graphAllNodes = $graph->getNodes();

        $numberOfEdges = 0;
        $numberOfNodes = count($graphAllNodes);
        foreach ($graphAllNodes as $nodeKey => $nodex) {
            $numberOfEdges += $nodex->inDegree();
            unset($nodex);
        }
        $numberOfEdges /= 2;

        $serializedGraph = serialize($graph);

        try {
            FlickrAnalyseModel::addSnapshot($numberOfNodes, $numberOfEdges, $serializedGraph);
            dibi::query('COMMIT');
            //$this->flashMessage("Snapshot byl úspěšně vytvořen.");
        } catch (Exception $e) {
            dibi::query('ROLLBACK');
            Debug::processException($e);
//            $this->flashMessage("Error description: " . $e->getMessage(), 'error');
        }
    }

}
