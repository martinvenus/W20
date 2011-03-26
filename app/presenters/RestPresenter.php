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

        if($accept === "application/xml"){
            $this->template->xmlData = "data";
            $httpResponse->setContentType('application/xml');
            $httpResponse->setCode(200);
        }
        else if($accept === "image/png"){
            $this->template->pngImage = file_get_contents('http://chart.apis.google.com/chart?chxl=2:|2.2.2011|3.2.2011|4.2.2011|5.2.2011&chxp=2,10,20,30,40&chxr=1,5,1000|2,0,50&chxs=0,0000FF,11.5,0,lt,676767|1,FF0000,10.5,0,lt,676767|2,676767,11.5,0,lt,676767&chxt=y,y,x&chs=440x220&cht=lc&chco=3072F3,FF0000&chds=0,95,5,10000&chd=t:31.632,34.959,40.744,37.756,40.435,41.035,44.194,45.377,42.034,42.628,41.8,46.582,48.749,57.068,58.369,59.024,58.598,54.261,55.468,59.006,58.215,55.865,53.043,52.268,55.427,55.816,57.494,58.241,58.931,62.027,63.903,66.405,65.694,66.375,69.256,69.174,66.625,66.5,68.884,63.389,62.863,63.448|3192.86,3115.708,2636.745,2270.452,1441.495,1477.353,1219.15,1688.579,1481.213,1768.386,2191.412,2590.794,2898.1,2807.987,2809.466,3244.751,4034.291,4578.481,4601.033,4835.196,4639.297,4163.523,3865.194,4094.998,4241.581,4252.596,4590.532,5015.837,5193.319,5600.66,6059.783,6251.31,6539.539,6615.218,6305.324,6183.891,6016.528,5435.99,5365.103,5507.907&chdl=Nodes|Edges&chdlp=b&chls=1|1&chma=5,5,5,25|40');
            $httpResponse->setContentType('image/png');
            $httpResponse->setCode(200);
        }
        else{
            $this->template->pngImage = file_get_contents('http://chart.apis.google.com/chart?chxl=2:|2.2.2011|3.2.2011|4.2.2011|5.2.2011&chxp=2,10,20,30,40&chxr=1,5,1000|2,0,50&chxs=0,0000FF,11.5,0,lt,676767|1,FF0000,10.5,0,lt,676767|2,676767,11.5,0,lt,676767&chxt=y,y,x&chs=440x220&cht=lc&chco=3072F3,FF0000&chds=0,95,5,10000&chd=t:31.632,34.959,40.744,37.756,40.435,41.035,44.194,45.377,42.034,42.628,41.8,46.582,48.749,57.068,58.369,59.024,58.598,54.261,55.468,59.006,58.215,55.865,53.043,52.268,55.427,55.816,57.494,58.241,58.931,62.027,63.903,66.405,65.694,66.375,69.256,69.174,66.625,66.5,68.884,63.389,62.863,63.448|3192.86,3115.708,2636.745,2270.452,1441.495,1477.353,1219.15,1688.579,1481.213,1768.386,2191.412,2590.794,2898.1,2807.987,2809.466,3244.751,4034.291,4578.481,4601.033,4835.196,4639.297,4163.523,3865.194,4094.998,4241.581,4252.596,4590.532,5015.837,5193.319,5600.66,6059.783,6251.31,6539.539,6615.218,6305.324,6183.891,6016.528,5435.99,5365.103,5507.907&chdl=Nodes|Edges&chdlp=b&chls=1|1&chma=5,5,5,25|40');
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
