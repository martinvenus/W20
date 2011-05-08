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
 * Rest presenter.
 *
 * @author     Martin Venuš
 * @package    W20
 */
class RestPresenter extends BasePresenter {

    public function renderDefault() {
        
    }

    /**
     * Metoda, která zajistí data pro Rest službu api/v1/metadata
     *
     * @param $lastUpdate čas poslední aktualizace v Unix timestamp
     */
    public function renderMetaData($lastUpdate = 0) {
        if ($lastUpdate < 0) {
            throw new Exception('Last update must be positive, negative given!');
        }

        $this->template->lastUpdate = Date('c', $lastUpdate);

        $httpResponse = \Nette\Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');
    }

    /**
     * Metoda, která zajistí data pro Rest službu api/v1/results
     */
    public function actionResults() {

// include struktury grafu (nutné pro unserialize)

        require_once 'Structures/Graph.php';

        $snapshoty = SnapshotModel::getSnapshots();


        foreach ($snapshoty as $snapshot => $graf) {

            //if($graf['id']>10) break;
            //$graf['id']=1;
            $graf_obj = unserialize(SnapshotModel::getGraph($graf['id']));
            //$graf_obj = unserialize(SnapshotModel::getGraph(1));

            $graphAllNodes = $graf_obj->getNodes();

            $g = new graf();

            foreach ($graphAllNodes as $nodeKey => $nodex) {

                $userInfo = $nodex->getMetadata('userInfo');

                // pridame uzel
                $g->addUzol($userInfo['username']);
            }

            foreach ($graphAllNodes as $nodeKey => $nodex) {

                $userInfo = $nodex->getMetadata('userInfo');

                // pridame hrany
                $arcs = $nodex->getNeighbours();
                for ($i = 0; $i < sizeof($arcs); $i++) {
                    $nData = $arcs[$i]->getMetadata('userInfo');
                    $ui = $nData['username'];
                    $g->addHrana($userInfo['username'], $ui);
                }
            }

            // erdos number
            $erdos_all = erdos($g, "Linus Gelber");
            $erdos_gr = array_sum($erdos_all) / count($erdos_all);
            $erdos[$graf['id']] = $erdos_gr;

            //clustering
            $clustering_all = clustering($g);
            $clustering_gr = array_sum($clustering_all) / count($clustering_all);
            $clustering[$graf['id']] = $clustering_gr;

            // overlap, embedeness
            $ne = neighborhood_embeddedness($g);
            $overlap_all = $ne["neighborhood"];
            $embeddedness_all = $ne["embeddedness"];

            $overlap_gr = array_sum($overlap_all) / count($overlap_all);
            $overlap[$graf['id']] = $overlap_gr;

            $embeddedness_gr = array_sum($embeddedness_all) / count($embeddedness_all);
            $embeddedness[$graf['id']] = $embeddedness_gr;

            // density

            $density[$graf['id']] = density($g);

            unset($graf_obj);
        }

        $this->template->erdos = $erdos;
        $this->template->clustering = $clustering;
        $this->template->overlap = $overlap;
        $this->template->embeddedness = $embeddedness;
        $this->template->density = $density;
        $this->template->xmlData = SnapshotModel::getSnapshots();

        $httpResponse = \Nette\Environment::getHttpResponse();
        $httpResponse->setContentType('application/xml');
    }

    /**
     * Metoda, která zajistí data pro Rest službu api/v1/metadata
     *
     * @param $lastUpdate čas poslední aktualizace v Unix timestamp
     */
    public function renderSnapshots() {

        $httpRequest = \Nette\Environment::getHttpRequest();
        $httpResponse = \Nette\Environment::getHttpResponse();

        $accept = $httpRequest->getHeader("Accept");

        $types = $this->parseAccept($accept);

        $accepted = 0;

        foreach ($types as $type => $q) {


            if ($type === "application/xml") {
                $this->template->xmlData = SnapshotModel::getSnapshots();
                $httpResponse->setContentType('application/xml');
                $httpResponse->setCode(200);
                $accepted = 1;
                break;
            } else if ($type === "image/png") {

                $data = SnapshotModel::getSnapshotsAsArray();

                $maxNodes = max($data['nodes']);
                $maxEdges = max($data['edges']);

                $this->template->pngImage = file_get_contents(ChartModel::drawChart($data['nodes'], $data['edges'], $data['times']));
                $httpResponse->setContentType('image/png');
                $httpResponse->setCode(200);
                $accepted = 1;
                break;
            }
        }

        if ($accepted == 0) {
            $this->template->error = "The service accepts either application/xml or image/png requests only.";
            $httpResponse->setCode(400);
        }
    }

    /**
     * Registrace API
     */
    public function actionRegisterAPI() {
        $data = '<?xml version="1.0" encoding="UTF-8"?>
<endPoint>
   <endPointUrl>http://w20.fit.cvut.wsolution.cz/api/</endPointUrl><!-- Endpoint base url: bez /v1/ -->
</endPoint>';

        $req = RestClientModel::put('http://mi-w20.appspot.com/api/endpoints/104-4', $data, null, null, 'application/xml');

        if ($req->getResponseCode() == 200) {

            $this->flashMessage('API bylo úspěšně zaregistrováno.');
        } else {
            $this->flashMessage('API se nepodařilo zaregistrovat.');
        }

        $this->redirect('Homepage:default');
    }

    private function parseAccept($accept) {

        $types = array();

// break up string into pieces (languages and q factors)
        preg_match_all('/([a-z]{1,15}(\\/[a-z]{1,15}))\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $accept, $accept_parse);

        if (count($accept_parse[1])) {
// vytvorime pole
            $types = array_combine($accept_parse[1], $accept_parse[4]);

// defaultni q faktor je 1
            foreach ($types as $type => $val) {
                if ($val === '')
                    $types[$type] = 1;
            }

// serazeni pole
            arsort($types, SORT_NUMERIC);
        }

        return $types;
    }

    /**
     * Metoda, která vrátí data ve formátu GEXF
     */
    public function renderGexf() {

        $httpRequest = \Nette\Environment::getHttpRequest();
        $httpResponse = \Nette\Environment::getHttpResponse();

        $accept = $httpRequest->getHeader("Accept");

        $types = $this->parseAccept($accept);

        $accepted = 0;

        foreach ($types as $type => $q) {

            if ($type === "application/xml") {

                $data = SnapshotModel::getDataList();

                $this->template->uzly = $data[0];
                $this->template->hrany = $data[1];
                $this->template->zmeneno = $data[2];

                $httpResponse->setContentType('application/xml');

                $httpResponse->setCode(200);
                $accepted = 1;
                break;
            }
        }

        if ($accepted == 0) {
            $this->template->error = "The service accepts application/xml requests only.";
            $httpResponse->setCode(400);
        }
    }

    /**
     * Metoda, která vrátí data ve formátu GEXF (FINAL)
     */
    public function renderGexfFinal() {

        $httpRequest = \Nette\Environment::getHttpRequest();
        $httpResponse = \Nette\Environment::getHttpResponse();

        $accept = $httpRequest->getHeader("Accept");

        $types = $this->parseAccept($accept);

        $accepted = 0;

//        foreach ($types as $type => $q) {
//
//            if ($type === "application/xml") {

                $data = SnapshotModel::getDataList();

                // HHHHHHHHHHHHHHHHHHHHHHHHHHHHH
                
                require_once 'Structures/Graph.php';

                $erdos = array();

                $snapshoty = SnapshotModel::getSnapshots();

                foreach ($snapshoty as $snapshot => $graf) {

                    //if($graf['id']>10) break;
                    //$graf['id']=1;
                    $graf_obj = unserialize(SnapshotModel::getGraph($graf['id']));
                    //$graf_obj = unserialize(SnapshotModel::getGraph(1));

                    $graphAllNodes = $graf_obj->getNodes();

                    $g = new graf();

                    foreach ($graphAllNodes as $nodeKey => $nodex) {

                        $userInfo = $nodex->getMetadata('userInfo');

                        // pridame uzel
                        $g->addUzol($userInfo['username']);
                    }

                    foreach ($graphAllNodes as $nodeKey => $nodex) {

                        $userInfo = $nodex->getMetadata('userInfo');

                        // pridame hrany
                        $arcs = $nodex->getNeighbours();
                        for ($i = 0; $i < sizeof($arcs); $i++) {
                            $nData = $arcs[$i]->getMetadata('userInfo');
                            $ui = $nData['username'];
                            $g->addHrana($userInfo['username'], $ui);
                        }
                    }

                    // erdos number
                    $erdos_all = erdos($g, "Linus Gelber");
                    $erdos[$graf['id']] = $erdos_all;

                    //clustering
                    $clustering_all = clustering($g);
                    $clustering[$graf['id']] = $clustering_all;

//                    // overlap, embedeness
//                    $ne = neighborhood_embeddedness($g);
//                    $overlap_all = $ne["neighborhood"];
//                    $embeddedness_all = $ne["embeddedness"];
//
//                    $overlap[$graf['id']] = $overlap_all;
//                    $embeddedness[$graf['id']] = $embeddedness_all;

//                    // density

                    $density[$graf['id']] = density($g);

                    unset($graf_obj);
                }


                $this->template->uzly = $data[0];
                $this->template->hrany = $data[1];
                $this->template->zmeneno = $data[2];
                $this->template->snapshotDetails = $snapshoty;
                $this->template->erdos = $erdos;
                $this->template->clustering = $clustering;
                //$this->template->overlap = $overlap;
                //$this->template->embeddedness = $embeddedness;

                $httpResponse->setContentType('application/xml');

                $httpResponse->setCode(200);
                $accepted = 1;
//                break;
//            }
//        }

        if ($accepted == 0) {
            $this->template->error = "The service accepts application/xml requests only.";
            $httpResponse->setCode(400);
        }
    }

}
