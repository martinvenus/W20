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

}
