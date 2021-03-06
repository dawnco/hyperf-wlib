<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-25
 */

namespace WLib\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WLib\WCtx;
use WLib\WLog;

class RpcLogMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $json = json_decode($request->getBody()->getContents(), true);
        $requestId = $json['params'][0] ?? '';
        WCtx::setRequestId($requestId);
        WLog::record("rpc-request", $json);
        $response = $handler->handle($request);
        WLog::record("rpc-response", $response->getBody()->getContents());
        return $response;
    }

}
