<?php

/**
 * Data mining system
 * MI-W20 at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2011
 * @package    W20
 * @author     Ján Januška, Jaroslav Líbal, Martin Venuš
 */
use Nette\Application\Presenter;
use Nette\Environment;

/**
 * Base class for all application presenters.
 *
 * @author     Martin Venuš
 * @package    W20
 */
abstract class BasePresenter extends Presenter {

    public $oldLayoutMode = FALSE;

    public function startup() {
        //Debug::timer();

        parent::startup();

        /* Připojení k databázi na základě údajů zadaných v config.ini */
        dibi::connect(Environment::getConfig('database'));

        /* Nechceme automatické potvrzování transakcí - budeme to dělat explicitně */
        dibi::query('SET AUTOCOMMIT=0');
    }

}
