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

namespace Bonefish\Router\Route;


final class Route implements RouteInterface
{

    /**
     * @var string|array
     */
    protected $httpMethods;

    /**
     * @var RouteCallbackDTOInterface
     */
    protected $dto;

    /**
     * @var string
     */
    protected $route;

    /**
     * @param string|array $httpMethods
     * @param RouteCallbackDTOInterface $dto
     * @param string $route
     */
    public function __construct($httpMethods, RouteCallbackDTOInterface $dto, $route)
    {
        $this->httpMethods = $httpMethods;
        $this->dto = $dto;
        $this->route = strtolower($route);
    }


    /**
     * @return string|array
     */
    public function getHttpMethods()
    {
        return $this->httpMethods;
    }

    /**
     * @return RouteCallbackDTOInterface
     */
    public function getDto()
    {
        return $this->dto;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }
}