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
 * @date       13.06.2015
 */

namespace Bonefish\Router\Collectors;


use Bonefish\Router\Route\Route;
use Bonefish\Router\Route\RouteInterface;

final class CombinedRouteCollector implements RouteCollector
{

    /**
     * @var RouteCollector[]
     */
    protected $collectors = [];

    /**
     * @return RouteCollector[]
     */
    public function getCollectors()
    {
        return $this->collectors;
    }

    /**
     * @param RouteCollector[] $collectors
     * @return self
     */
    public function setCollectors($collectors)
    {
        foreach($collectors as $collector) {
            $this->addCollector($collector);
        }

        return $this;
    }

    /**
     * @param RouteCollector $collector
     * @return self
     */
    public function addCollector(RouteCollector $collector)
    {
        $this->collectors[] = $collector;

        return $this;
    }

    /**
     * Aggregate routes and return an array of Route DTOs
     *
     * @return RouteInterface[]
     */
    public function collectRoutes()
    {
        $routes = [];

        foreach($this->getCollectors() as $collector) {
            $routes = array_merge($routes, $collector->collectRoutes());
        }

        return $routes;
    }
}