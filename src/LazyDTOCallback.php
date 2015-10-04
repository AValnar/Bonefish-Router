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
 * @date       04.10.2015
 */

namespace Bonefish\Router;


final class LazyDTOCallback
{
    /**
     * @var string
     */
    private $className;


    /**
     * @var string
     */
    private $action;

    /**
     * @param string $className
     * @param string $action
     */
    public function __construct($className, $action)
    {
        $this->className = $className;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param array $state
     * @return LazyDTOCallback
     */
    public static function __set_state($state)
    {
        return new self($state['className'], $state['action']);
    }


}