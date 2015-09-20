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


final class RouteCallbackDTO implements RouteCallbackDTOInterface
{

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $suppliedParameters;

    /**
     * @param callable $callback
     * @param array $parameters
     */
    public function __construct(callable $callback, array $parameters = [])
    {
        $this->callback = $callback;
        $this->parameters = $parameters;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }


    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getSuppliedParameters()
    {
        return $this->suppliedParameters;
    }

    /**
     * @param array $suppliedParameters
     */
    public function setSuppliedParameters($suppliedParameters)
    {
        $this->suppliedParameters = $suppliedParameters;
    }

    /**
     * @param array $parameters
     * @return RouteCallbackDTOInterface
     */
    public static function __set_state(array $parameters)
    {
        return new self($parameters['callback'], $parameters['parameters']);
    }


}