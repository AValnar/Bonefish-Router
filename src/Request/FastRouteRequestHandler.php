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
     * @param FastRoute $router
     * @param RouteCollector $routeCollector
     */
    public function __construct(FastRoute $router, RouteCollector $routeCollector)
    {
        $this->router = $router;
        $this->routeCollector = $routeCollector;
    }


    public function handleRequest(RequestInterface $request)
    {
        $this->router->addDefaultHandler(
            new RouteCallbackDTO(function(){
                echo 'Hello World!';
            })
        );
        $this->router->addErrorHandler(
            404,
            new RouteCallbackDTO(function(){
                echo '404!';
            })
        );
        $this->router->addErrorHandler(
            405,
            new RouteCallbackDTO(function(){
                echo '405';
            })
        );

        if (!file_exists($this->router->getCacheFilePath())) {
            $this->router->addRoutes($this->routeCollector->collectRoutes());
        }

        $dispatcherResultHandler = $this->router->dispatch($request)->getHandler();

        $suppliedParameters = $dispatcherResultHandler->getSuppliedParameters();
        $sortedParameters = [];

        foreach($dispatcherResultHandler->getParameters() as $parameter => $optional) {
            if (isset($suppliedParameters[$parameter])) {
                $sortedParameters[] = $suppliedParameters[$parameter];
            }
        }

        call_user_func($dispatcherResultHandler->getCallback(), ...$sortedParameters);

    }
}