<?php

declare(strict_types=1);

namespace Marko\Routing;

use Marko\Core\Container\ContainerInterface;
use Marko\Core\Path\ProjectPaths;
use Marko\Core\Plugin\PluginInterceptedInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Routing\Middleware\MiddlewareInterface;
use Marko\Routing\Middleware\MiddlewarePipeline;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;

readonly class Router
{
    private RouteMatcher $matcher;

    private MiddlewarePipeline $pipeline;

    /**
     * @param array<class-string<MiddlewareInterface>> $globalMiddleware
     */
    public function __construct(
        RouteCollection $routes,
        private ContainerInterface $container,
        private array $globalMiddleware = [],
    ) {
        $this->matcher = new RouteMatcher($routes);
        $this->pipeline = new MiddlewarePipeline($container);
    }

    /**
     * @throws ContainerExceptionInterface|ReflectionException
     */
    public function handle(
        Request $request,
    ): Response {
        $matched = $this->matcher->match($request->method(), $request->path());

        if ($matched === null) {
            file_put_contents('/tmp/router_debug.log', "NO_MATCH: " . $request->method() . ' ' . $request->path() . "\n", FILE_APPEND);
            return $this->renderNotFoundResponse($request);
        }
        file_put_contents('/tmp/router_debug.log', "MATCHED: " . $request->path() . "\n", FILE_APPEND);

        $handler = function (Request $request) use ($matched): Response {
            $controller = $this->container->get($matched->route->controller);

            $parameters = $this->resolveParameters(
                $controller,
                $matched->route->action,
                $matched->parameters,
                $request,
            );

            $result = $controller->{$matched->route->action}(...$parameters);

            return $this->wrapResult($result);
        };

        $middleware = [...$this->globalMiddleware, ...$matched->route->middleware];

        return $this->pipeline->process(
            $middleware,
            $request,
            $handler,
        );
    }

    /**
     * Resolve controller method parameters from route params, POST data, and query string.
     *
     * @param array<string, mixed> $routeParams
     * @return array<mixed>
     * @throws ReflectionException
     */
    private function resolveParameters(
        object $controller,
        string $action,
        array $routeParams,
        Request $request,
    ): array {
        // Unwrap interceptor to reflect on the real controller
        $reflectionTarget = $controller instanceof PluginInterceptedInterface
            ? $controller->getPluginTarget()
            : $controller;
        $reflection = new ReflectionMethod($reflectionTarget, $action);
        $parameters = [];

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            // Inject Request object when type-hinted
            if ($type instanceof ReflectionNamedType && $type->getName() === Request::class) {
                $parameters[] = $request;
                continue;
            }

            // Priority: route params > POST data > query string > default
            if (array_key_exists($name, $routeParams)) {
                $parameters[] = $this->castToType($routeParams[$name], $type);
            } elseif (($postValue = $request->post($name)) !== null) {
                $parameters[] = $postValue;
            } elseif (($queryValue = $request->query($name)) !== null) {
                $parameters[] = $queryValue;
            } elseif ($param->isDefaultValueAvailable()) {
                $parameters[] = $param->getDefaultValue();
            } else {
                $parameters[] = null;
            }
        }

        return $parameters;
    }

    private function castToType(
        mixed $value,
        ?ReflectionType $type,
    ): mixed {
        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        return match ($type->getName()) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            default => $value,
        };
    }

    private function wrapResult(
        mixed $result,
    ): Response {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            return new Response($result);
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return new Response((string) $result);
    }

    /**
     * Render a 404 response using the self-contained errors/404 template.
     */
    private function renderNotFoundResponse(Request $request): Response
    {
        $template = 'pages/errors/404';
        $projectPaths = $this->container->get(ProjectPaths::class);
        $viewPath = $projectPaths->templates . '/' . $template . '.php';

        if (!is_file($viewPath)) {
            return new Response('<h1>404 - Page Not Found</h1><pre>DEBUG: viewPath=' . $viewPath . ' exists=' . (is_file($viewPath) ? 'yes' : 'no') . '</pre>', 404);
        }

        $data = [
            'heading'     => 'Page not found',
            'description' => 'The page you are looking for does not exist.',
        ];

        extract($data, EXTR_SKIP);
        ob_start();
        include $viewPath;
        $content = ob_get_clean() ?: '';

        return new Response($content, 404);
    }
}
