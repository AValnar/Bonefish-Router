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

use Bonefish\Injection\Container\ContainerInterface;
use Bonefish\Router\Collectors\RouteCollector;
use Bonefish\Router\FastRoute;
use Bonefish\Router\LazyDTOCallback;
use Bonefish\Router\Route\RouteCallbackDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     */
    public function __construct(FastRoute $router, RouteCollector $routeCollector, ContainerInterface $container)
    {
        $this->router = $router;
        $this->routeCollector = $routeCollector;
        $this->container = $container;
    }


    public function handleRequest(Request $request)
    {
        $this->router->addDefaultHandler(
            new RouteCallbackDTO(function () {
                return new Response("Hello World");
            })
        );
        $this->router->addErrorHandler(
            404,
            new RouteCallbackDTO(function () {
                return new Response("Not found!", 404);
            })
        );
        $this->router->addErrorHandler(
            405,
            new RouteCallbackDTO(
                function ($allowedMethods) use ($request) {
                    return new Response("Method " . $request->getMethod() . " is not allowed! Use one of these: " . implode(',', $allowedMethods), 405);
                },
                ['allowedMethods' => false]
            )
        );

        if (!file_exists($this->router->getCacheFilePath())) {
            $this->router->addRoutes($this->routeCollector->collectRoutes());
        }

        $dispatcherResultHandler = $this->router->dispatch($request)->getHandler();

        $suppliedParameters = $dispatcherResultHandler->getSuppliedParameters();
        $sortedParameters = [];

        foreach ($dispatcherResultHandler->getParameters() as $parameter => $optional) {
            if (isset($suppliedParameters[$parameter])) {
                $sortedParameters[] = $suppliedParameters[$parameter];
            } elseif (!$optional) {
                $sortedParameters[] = $request->request->get($parameter);
            }
        }

        $callback = $dispatcherResultHandler->getCallback();

        if ($callback instanceof LazyDTOCallback)
        {
            $callback = [$this->container->get($callback->getClassName()), $callback->getAction()];
        }

        return call_user_func($callback, ...$sortedParameters);
    }
}