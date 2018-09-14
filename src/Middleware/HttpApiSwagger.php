<?php

namespace Reliv\SwaggerExpressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reliv\SwaggerExpressive\Api\BuildSwaggerConfig;
use Reliv\SwaggerExpressive\Api\IsSwaggerRoute;
use Zend\Diactoros\Response\JsonResponse;

/**
 * @todo   Add caching
 *
 * @author James Jervis - https://github.com/jerv13
 */
class HttpApiSwagger implements MiddlewareInterface
{
    protected $routeConfig;
    protected $swaggerConfig;
    protected $isSwaggerRoute;
    protected $buildSwaggerConfig;
    protected $debug;

    /**
     * @param array              $routeConfig
     * @param array              $swaggerConfig
     * @param IsSwaggerRoute     $isSwaggerRoute
     * @param BuildSwaggerConfig $buildSwaggerConfig
     * @param bool               $debug
     */
    public function __construct(
        array $routeConfig,
        array $swaggerConfig,
        IsSwaggerRoute $isSwaggerRoute,
        BuildSwaggerConfig $buildSwaggerConfig,
        bool $debug = false
    ) {
        $this->routeConfig = $routeConfig;
        $this->swaggerConfig = $swaggerConfig;
        $this->isSwaggerRoute = $isSwaggerRoute;
        $this->buildSwaggerConfig = $buildSwaggerConfig;
        $this->debug = $debug;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable|null          $next
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate = null
    ) {
        $routeConfig = array_filter(
            $this->routeConfig,
            [$this, 'filterRoutes'],
            ARRAY_FILTER_USE_BOTH
        );

        ksort($routeConfig);

        $this->swaggerConfig['host'] = $request->getUri()->getHost();

        $this->swaggerConfig = $this->buildSwaggerConfig->__invoke(
            $this->swaggerConfig,
            [BuildSwaggerConfig::OPTION_ROUTE_CONFIG => $routeConfig]
        );

        return new JsonResponse(
            $this->swaggerConfig,
            200,
            [],
            $this->getJsonFlags()
        );
    }

    /**
     * @param array $routeData
     * @param mixed $key
     *
     * @return bool
     */
    protected function filterRoutes($routeData, $key)
    {
        return $this->isSwaggerRoute->__invoke($key, $routeData);
    }

    /**
     * @return int
     */
    public function getJsonFlags()
    {
        if ($this->debug) {
            return JSON_PRETTY_PRINT | JsonResponse::DEFAULT_JSON_FLAGS;
        }

        return JsonResponse::DEFAULT_JSON_FLAGS;
    }
}
