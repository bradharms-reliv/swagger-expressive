<?php

namespace Reliv\SwaggerExpressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reliv\SwaggerExpressive\Api\IsAllowedSwagger;
use Zend\Diactoros\Response\JsonResponse;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class HttpApiIsAllowedSwagger implements MiddlewareInterface
{
    const SOURCE = 'swagger-is-allowed-check-api';

    const DEFAULT_NOT_ALLOWED_STATUS = 401;

    protected $isAllowed;
    protected $isAllowedOptions;
    protected $notAllowedStatus;
    protected $debug;

    /**
     * @param IsAllowedSwagger $isAllowed
     * @param array            $isAllowedOptions
     * @param int              $notAllowedStatus
     * @param bool             $debug
     */
    public function __construct(
        IsAllowedSwagger $isAllowed,
        array $isAllowedOptions,
        int $notAllowedStatus = self::DEFAULT_NOT_ALLOWED_STATUS,
        bool $debug = false
    ) {
        $this->isAllowed = $isAllowed;
        $this->isAllowedOptions = $isAllowedOptions;
        $this->notAllowedStatus = $notAllowedStatus;
        $this->debug = $debug;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface|null $delegate
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate = null
    ) {
        if (!$this->isAllowed->__invoke($request, $this->isAllowedOptions)) {
            return new JsonResponse(
                [],
                $this->notAllowedStatus,
                [],
                $this->getJsonFlags()
            );
        }

        return $delegate->process($request);
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
