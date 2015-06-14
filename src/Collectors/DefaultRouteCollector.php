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

namespace Bonefish\Router\Collectors;


use Bonefish\Injection\Annotations\Inject;
use Bonefish\Reflection\Meta\MethodMeta;
use Bonefish\Reflection\ReflectionService;
use Bonefish\Router\Route\Route;
use Bonefish\Router\Route\RouteCallbackDTO;
use Bonefish\Router\Route\RouteCallbackDTOInterface;
use Bonefish\Router\Route\RouteInterface;
use Bonefish\Utility\Environment;
use Nette\Reflection\AnnotationsParser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class DefaultRouteCollector implements RouteCollector
{

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    const ACTION_SUFFIX = 'Action';
    const DEFAULT_HTTP_METHOD = 'GET';

    /**
     * @param Finder $finder
     * @param Environment $environment
     * @param ReflectionService $reflectionService
     * @Inject
     */
    public function __construct(
        Finder $finder,
        Environment $environment,
        ReflectionService $reflectionService
    )
    {
        $this->finder = $finder;
        $this->environment = $environment;
        $this->reflectionService = $reflectionService;
    }

    /**
     * @return RouteInterface[]
     */
    public function collectRoutes()
    {
        $routes = [];

        $dtos = $this->buildDTOs();

        /** @var RouteCallbackDTOInterface $dto */
        foreach($dtos as $dto)
        {
            $routePath = $this->getBaseRouteForDTO($dto);
            $parameters = $dto->getParameters();

            // No parameters so just generate default route and continue
            if (count($parameters) == 0) {
                $routes[] = new Route(self::DEFAULT_HTTP_METHOD, $dto, $routePath);
                continue;
            }

            // Loop parameters and add new route if optional parameter
            foreach($dto->getParameters() as $parameterName => $isParameterOptional)
            {
                if ($isParameterOptional) {
                    $routes[] = new Route(self::DEFAULT_HTTP_METHOD, $dto, $routePath);
                }

                $routePath .= '/{' . $parameterName . '}';
            }

            // Add possible last route ( all required parameters or last optional one )
            $routes[] = new Route(self::DEFAULT_HTTP_METHOD, $dto, $routePath);
        }

        return $routes;
    }

    protected function collectControllers()
    {
        $controllers = [];

        $packagesPath = $this->environment->getFullPackagePath();
        $vendorPath = $this->environment->getBasePath() . '/vendor';

        $this->finder->files()
            ->ignoreUnreadableDirs()
            ->in($packagesPath)
            ->in($vendorPath)
            ->exclude('/tests/i')
            ->path('/controller/i')
            ->name('*Controller.php');

        /** @var SplFileInfo $file */
        foreach ($this->finder as $file) {
            $parsed = AnnotationsParser::parsePhp(file_get_contents($file->getPathname()));
            $class = array_keys($parsed);
            $controllers[] = $class[0];
        }

        return $controllers;
    }

    /**
     * @param string $controller
     * @return MethodMeta
     */
    protected function collectActions($controller)
    {
        $actions = [];

        $classMeta = $this->reflectionService->getClassMetaReflection($controller);

        foreach ($classMeta->getMethods() as $methodMeta) {

            // skip if method is not an action or an inherited action
            if (!stristr($methodMeta->getName(), self::ACTION_SUFFIX) ||
                $methodMeta->getDeclaringClass() !== $classMeta) {
                continue;
            }

            $actions[] = $methodMeta;
        }

        return $actions;
    }

    protected function buildDTOs()
    {
        $dtos = [];

        $controllers = $this->collectControllers();

        foreach($controllers as $controller)
        {
            $actions = $this->collectActions($controller);

            /** @var MethodMeta $action */
            foreach($actions as $action)
            {
                $parameters = $this->getParametersFromAction($action);
                $dtos[] = new RouteCallbackDTO($controller, $action->getName(), $parameters);
            }
        }

        return $dtos;
    }

    /**
     * @param MethodMeta $action
     * @return array
     */
    protected function getParametersFromAction(MethodMeta $action)
    {
        $parameters = [];

        foreach($action->getParameters() as $parameter)
        {
            $parameters[$parameter->getName()] = $parameter->isOptional();
        }

        return $parameters;
    }

    /**
     * @param RouteCallbackDTOInterface $dto
     * @return string
     */
    protected function getBaseRouteForDTO(RouteCallbackDTOInterface $dto)
    {
        $classMeta = $this->reflectionService->getClassMetaReflection($dto->getController());
        $nameSpaceParts = explode('\\', $classMeta->getNamespace());
        // /vendor/package/controller/action
        return '/' . $nameSpaceParts[0] . '/'. $nameSpaceParts[1] . '/' . $classMeta->getShortName() . '/' . str_replace('Action', '', $dto->getAction());
    }

}