<?php

/**
 * Data mining system
 * MI-W20 at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2011
 * @package    W20
 * @author     Ján Januška, Jaroslav Líbal, Martin Venuš
 */
use Nette\Debug,
 Nette\Application\BadRequestException;

/**
 * Error presenter.
 *
 * @package    W20
 */
class ErrorPresenter extends BasePresenter {

    /**
     * @param  Exception
     * @return void
     */
    public function renderDefault($exception) {
        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = TRUE;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $this->setView('404'); // load template 404.phtml
        } else {
            $this->setView('500'); // load template 500.phtml
            Debug::processException($exception); // and handle error by Nette\Debug
        }
    }

}
