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

        if ($accept === "application/xml") {
            $this->template->xmlData = SnapshotModel::getSnapshots();
            $httpResponse->setContentType('application/xml');
            $httpResponse->setCode(200);
        } else if ($accept === "image/png") {

            $data = SnapshotModel::getSnapshotsAsArray();
            
            $maxNodes = max($data['nodes']);
            $maxEdges = max($data['edges']);
            
            $this->template->pngImage = file_get_contents(ChartModel::drawChart($data['nodes'], $data['edges'], $data['times']));
            $httpResponse->setContentType('image/png');
            $httpResponse->setCode(200);
        } else {
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

}
