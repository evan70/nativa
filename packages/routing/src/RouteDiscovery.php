<?php

declare(strict_types=1);

namespace Marko\Routing;

use Error;
use Marko\Core\Exceptions\MarkoException;
use Marko\Core\Module\ModuleManifest;
use Marko\Routing\Attributes\DisableRoute;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Attributes\Route;
use Marko\Routing\Exceptions\RouteException;
use ReflectionClass;
use ReflectionMethod;

class RouteDiscovery
{
    /**
     * Discover routes in a module's src directory.
     *
     * @return array<RouteDefinition>
     */
    public function discoverInModule(
        ModuleManifest $manifest,
    ): array {
        return [];
    }

    /**
     * Discover routes from a specific class.
     *
     * @param class-string $className
     * @return array<RouteDefinition>
     */
    public function discoverFromClass(
        string $className,
    ): array {
        $routes = [];
        $reflection = new ReflectionClass($className);
        $classMiddleware = $this->getClassMiddleware($reflection);

        foreach ($reflection->getMethods() as $method) {
            if ($this->isRouteDisabled($method)) {
                continue;
            }

            // Check for generic Route attribute first
            foreach ($method->getAttributes() as $attribute) {
                try {
                    $instance = $attribute->newInstance();
                } catch (Error $e) {
                    $missingClass = MarkoException::extractMissingClass($e);
                    if ($missingClass !== null) {
                        // Skip attributes from uninstalled Marko packages
                        if (MarkoException::inferPackageName($missingClass) !== null) {
                            continue;
                        }
                        throw RouteException::attributeClassNotFound($className, $missingClass, $e);
                    }
                    throw $e;
                }

                if ($instance instanceof Route) {
                    $methodMiddleware = $this->getMethodMiddleware($method);
                    $routes[] = new RouteDefinition(
                        method: $instance->getMethod(),
                        path: $instance->path,
                        controller: $className,
                        action: $method->getName(),
                        middleware: array_merge($classMiddleware, $instance->middleware, $methodMiddleware),
                    );
                    continue 2;
                }
            }

            // Check for concrete route attributes (Get, Post, Put, Patch, Delete)
            foreach (['Get', 'Post', 'Put', 'Patch', 'Delete'] as $methodType) {
                $concreteClass = 'Marko\\Routing\\Attributes\\' . $methodType;

                if (!class_exists($concreteClass, false)) {
                    continue;
                }

                $attrs = $method->getAttributes($concreteClass);

                if (!empty($attrs)) {
                    try {
                        $instance = $attrs[0]->newInstance();
                        $methodMiddleware = $this->getMethodMiddleware($method);
                        $routes[] = new RouteDefinition(
                            method: $instance->getMethod(),
                            path: $instance->path,
                            controller: $className,
                            action: $method->getName(),
                            middleware: array_merge($instance->middleware, $methodMiddleware),
                        );
                    } catch (Error $e) {
                        $missingClass = MarkoException::extractMissingClass($e);
                        if ($missingClass !== null) {
                            // Skip attributes from uninstalled Marko packages
                            if (MarkoException::inferPackageName($missingClass) !== null) {
                                continue;
                            }
                            throw RouteException::attributeClassNotFound($className, $missingClass, $e);
                        }
                        throw $e;
                    }
                    break;
                }
            }
        }

        return $routes;
    }

    /**
     * Get middleware defined at the class level.
     *
     * @return array<string>
     */
    private function getClassMiddleware(
        ReflectionClass $reflection,
    ): array {
        $middlewareAttributes = $reflection->getAttributes(Middleware::class);
        if (empty($middlewareAttributes)) {
            return [];
        }

        try {
            $middleware = $middlewareAttributes[0]->newInstance();
        } catch (Error $e) {
            $missingClass = MarkoException::extractMissingClass($e);
            if ($missingClass !== null) {
                if (MarkoException::inferPackageName($missingClass) !== null) {
                    return [];
                }
                throw RouteException::attributeClassNotFound($reflection->getName(), $missingClass, $e);
            }
            throw $e;
        }

        return $middleware->middleware;
    }

    /**
     * Get middleware defined at the method level.
     *
     * @return array<string>
     */
    private function getMethodMiddleware(
        ReflectionMethod $method,
    ): array {
        $middlewareAttributes = $method->getAttributes(Middleware::class);
        if (empty($middlewareAttributes)) {
            return [];
        }

        try {
            $middleware = $middlewareAttributes[0]->newInstance();
        } catch (Error $e) {
            $missingClass = MarkoException::extractMissingClass($e);
            if ($missingClass !== null) {
                if (MarkoException::inferPackageName($missingClass) !== null) {
                    return [];
                }
                $controller = $method->getDeclaringClass()->getName();
                throw RouteException::attributeClassNotFound($controller, $missingClass, $e);
            }
            throw $e;
        }

        return $middleware->middleware;
    }

    /**
     * Check if a method has the DisableRoute attribute.
     */
    private function isRouteDisabled(
        ReflectionMethod $method,
    ): bool {
        return !empty($method->getAttributes(DisableRoute::class));
    }
}