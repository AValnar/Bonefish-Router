<?php
/**
 * Copyright (C) 2015  Alexander Schmidt
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @author     Alexander Schmidt <mail@story75.com>
 * @copyright  Copyright (c) 2015, Alexander Schmidt
 * @date       14.06.2015
 */

namespace Bonefish\Router\Request;

use Bonefish\HelloWorld\Controller\Controller;
use Bonefish\Injection\Annotations\Inject;
use Bonefish\Injection\ContainerInterface;
use Bonefish\Router\Collectors\RouteCollector;
use Bonefish\Router\FastRoute;
use Bonefish\Router\Route\RouteCallbackDTO;

final class FastRouteRequestHandler implements RequestHandlerInterface
{
    /**
     * @var FastRoute
     */
    protected $router;

    /**
     * @var RouteCollector
     */
    protected $routeCollector;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param FastRoute $router
     * @param RouteCollector $routeCollector
     * @param ContainerInterface $container
     * @Inject
     */
    public function __construct(FastRoute $router, RouteCollector $routeCollector, ContainerInterface $container)
    {
        $this->router = $router;
        $this->routeCollector = $routeCollector;
        $this->container = $container;
    }


    public function handleRequest(RequestInterface $request)
    {
        $defaultHandler = new RouteCallbackDTO(Controller::class, 'indexAction');

        $this->router->addDefaultHandler($defaultHandler);
        $this->router->addErrorHandler(404, $defaultHandler);
        $this->router->addErrorHandler(405, $defaultHandler);

        if (!file_exists($this->router->getCacheFilePath())) {
            $this->router->addRoutes($this->routeCollector->collectRoutes());
        }

        $dispatcherResult = $this->router->dispatch($request);

        $controllerClass = $dispatcherResult->getHandler()->getController();
        $controllerObject = $this->container->get($controllerClass);

        $action = $dispatcherResult->getHandler()->getAction();

        $suppliedParameters = $dispatcherResult->getHandler()->getSuppliedParameters();
        $sortedParameters = [];

        foreach($dispatcherResult->getHandler()->getParameters() as $parameter => $optional) {
            if (isset($suppliedParameters[$parameter])) {
                $sortedParameters[] = $suppliedParameters[$parameter];
            }
        }

        echo $dispatcherResult->getHttpResponseCode();

        $controllerObject->{$action}(...$sortedParameters);
    }
}