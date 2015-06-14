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
 * @date       12.06.2015
 */

namespace Bonefish\Router;


use Bonefish\Injection\Annotations\Inject;
use Bonefish\Router\Request\RequestInterface;
use Bonefish\Router\Route\RouteCallbackDTOInterface;
use Bonefish\Router\Route\RouteInterface;
use Bonefish\Utility\Environment;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

final class FastRoute implements Router
{

    /**
     * @var RouteInterface
     */
    protected $routes = [];

    /**
     * @var RouteCallbackDTOInterface
     */
    protected $errorHandlers = [];

    /**
     * @var RouteCallbackDTOInterface
     */
    protected $defaultHandler;

    /**
     * @var Environment
     */
    protected $environment;

    const CACHE_FILE = '/fastroute.routes.cache';

    /**
     * @param Environment $environment
     * @Inject
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }


    /**
     * Add all routes which are available
     *
     * @param RouteInterface[] $routes
     * @return void
     */
    public function addRoutes(array $routes = [])
    {
        foreach($routes as $route)
        {
            if ($route instanceof RouteInterface) $this->routes[] = $route;
        }
    }

    /**
     * Add error handler for http status code
     *
     * @param int $httpStatusCode
     * @param RouteCallbackDTOInterface $handler
     * @return void
     */
    public function addErrorHandler($httpStatusCode, RouteCallbackDTOInterface $handler)
    {
        $this->errorHandlers[$httpStatusCode] = $handler;
    }

    /**
     * Add a default handler
     *
     * @param RouteCallbackDTOInterface $handler
     * @return void
     */
    public function addDefaultHandler(RouteCallbackDTOInterface $handler)
    {
        $this->defaultHandler = $handler;
    }

    /**
     * Once called the router should examine the current request by request method and url
     * and find a matching route. If no route was find dispatch a http error instead.
     *
     * Dispatch means in this context that the router will use the RouteCallbackDTO in the Route
     * and call the controller with the correct action and pass the parameters.
     *
     * @param RequestInterface $request
     * @return DispatcherResultInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if ($request->getUri() === '' || $request->getUri() === '/') {
            return new DispatcherResult(200, $this->defaultHandler);
        }

        $dispatcher = $this->getDispatcher();

        $match = $dispatcher->dispatch($request->getMethod(), $request->getUri());

        $code = null;
        $handler = null;

        switch ($match[0]) {
            case Dispatcher::NOT_FOUND:
                $code = 404;
                $handler = $this->errorHandlers[$code];
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $code = 405;
                $handler = $this->errorHandlers[$code];
                $handler->setSuppliedParameters(['allowedMethods' => $match[1]]);
                break;
            case Dispatcher::FOUND:
                $code = 200;
                /** @var RouteCallbackDTOInterface $handler */
                $handler = $match[1];
                $handler->setSuppliedParameters($match[2]);
                break;
        }

        if ($code !== null) {
            return new DispatcherResult($code, $handler);
        }

        return new DispatcherResult(200, $this->defaultHandler);
    }

    protected function getDispatcher()
    {
        $cachePath = $this->getCacheFilePath();

        $routes = $this->routes;

        $dispatcher = \FastRoute\cachedDispatcher(function(RouteCollector $r) use ($routes) {
            foreach($routes as $route) {
                $r->addRoute($route->getHttpMethods(), $route->getRoute(), $route->getDto());
            }
        }, [
            'cacheFile' => $cachePath
        ]);

        return $dispatcher;
    }

    public function getCacheFilePath()
    {
        return $this->environment->getFullCachePath() . self::CACHE_FILE;
    }


}