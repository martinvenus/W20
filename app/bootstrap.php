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
 Nette\Environment,
 Nette\Application\Route,
 Nette\Application\SimpleRouter;

// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';



// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable(Debug::DETECT, APP_DIR . '/../../log/error.log');

// 2b) load configuration from config.ini file
Environment::loadConfig();



// Step 3: Configure application
// 3a) get and setup a front controller
$application = Environment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;
// Step 4: Setup application router
$router = $application->getRouter();

$application->router[] = new Nette\Application\CliRouter(array(
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL,
        ));

$application->allowedMethods = NULL;

$router[] = new Route('index.php', array(
            'presenter' => 'Homepage',
            'action' => 'default',
                ), Route::ONE_WAY);

$router[] = new RestRoute('api/v1/metadata', array(
            'presenter' => 'Rest',
            'action' => 'metadata',
                ), RestRoute::METHOD_GET);

$router[] = new RestRoute('api/v1/snapshots', array(
            'presenter' => 'Rest',
            'action' => 'snapshots',
                ), RestRoute::METHOD_GET);

$router[] = new Route('<presenter>/<action>/<id>', array(
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL,
        ));

// Step 5: Run the application!
$application->run();
