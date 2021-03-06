<?php
/**
 * Data mining system
 * MI-W20 at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2011
 * @package    W20
 * @author     Ján Januška, Jaroslav Líbal, Martin Venuš
 */

use Nette\Application\Route;

/**
 * RestRouteModel
 *
 * @author     Martin Venuš
 * @package    W20
 */
class RestRoute extends Route
{
    const METHOD_POST = 4;
    const METHOD_GET = 8;
    const METHOD_PUT = 16;
    const METHOD_DELETE = 32;
    const RESTFUL = 64;

    public function match(\Nette\Web\IHttpRequest $httpRequest)
    {
        $httpMethod = $httpRequest->getMethod();

        if (($this->flags & self::RESTFUL) == self::RESTFUL) {
            $presenterRequest = parent::match($httpRequest);
            if ($presenterRequest != NULL) {
                switch ($httpMethod) {
                    case 'GET':
                        $action = 'default';
                        break;
                    case 'POST':
                        $action = 'create';
                        break;
                    case 'PUT':
                        $action = 'update';
                        break;
                    case 'DELETE':
                        $action = 'delete';
                        break;
                    default:
                        $action = 'default';
                }

                $params = $presenterRequest->getParams();
                $params['action'] = $action;
                $presenterRequest->setParams($params);
                return $presenterRequest;
            } else {
                return NULL;
            }
        }

        if (($this->flags & self::METHOD_POST) == self::METHOD_POST
            && $httpMethod != 'POST') {
                return NULL;
        }

        if (($this->flags & self::METHOD_GET) == self::METHOD_GET
            && $httpMethod != 'GET') {
                return NULL;
        }

        if (($this->flags & self::METHOD_PUT) == self::METHOD_PUT
            && $httpMethod != 'PUT') {
                return NULL;
        }

        if (($this->flags & self::METHOD_DELETE) == self::METHOD_DELETE
            && $httpMethod != 'DELETE') {
                return NULL;
        }
        
        return parent::match($httpRequest);
    }
}