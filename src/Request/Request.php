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


final class Request implements RequestInterface
{
    protected $validMethods = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD'];

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @param string $method
     * @param string $uri
     */
    public function __construct($method, $uri)
    {
        $this->method = $this->validateMethod($method);
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }


    /**
     * @return Request
     */
    public static function fromServer()
    {
        return new self(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI']
        );
    }

    /**
     * Validate method if invalid return default method
     *
     * @param string $method
     * @return string
     */
    protected function validateMethod($method)
    {
        $method = strtoupper($method);

        if (!in_array($method, $this->validMethods)) {
            return $this->validMethods[0];
        }

        return $method;
    }
}