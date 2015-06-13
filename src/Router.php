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


use Bonefish\Router\Route\Route;
use Bonefish\Router\Route\RouteCallbackDTO;

interface Router
{
    /**
     * Add all routes which are available
     *
     * @param Route[] $routes
     * @return void
     */
    public function addRoutes(array $routes);

    /**
     * Add error handler for http status code
     *
     * @param int $httpStatusCode
     * @param RouteCallbackDTO $handler
     * @return void
     */
    public function addErrorHandler($httpStatusCode, RouteCallbackDTO $handler);

    /**
     * Add a default handler
     *
     * @param RouteCallbackDTO $handler
     * @return void
     */
    public function addDefaultHandler(RouteCallbackDTO $handler);

    /**
     * Once called the router should examine the current request by request method and url
     * and find a matching route. If no route was find dispatch a http error instead.
     *
     * @param string $httpMethod
     * @param string $url
     * @return DispatcherResult
     */
    public function dispatch($httpMethod , $url);
}