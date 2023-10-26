<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Config;

use Closure;
use MedyaT\Parapos\Models\Payment;

final class Http
{
    /**
     * @param  string[]|Closure[]  $middlewares
     */
    final public function __construct(public Config $config, public array $middlewares = [])
    {

    }

    /**
     * @param  string[]  $headers
     */
    public function get(Payment $payment, string $uri, array $headers = []): HttpResponse
    {

        return $this->callWithMiddlewares(payment: $payment, uri: $uri, method: 'GET', headers: $headers);
    }

    /**
     * @param  string[]  $headers
     * @param  mixed[]  $params
     */
    public function post(Payment $payment, string $uri, array $params = [], array $headers = []): HttpResponse
    {
        return $this->callWithMiddlewares(payment: $payment, uri: $uri, method: 'POST', headers: $headers, params: $params);
    }

    /**
     * @param  string[]  $headers
     * @param  mixed[]  $params
     */
    public function put(Payment $payment, string $uri, array $params = [], array $headers = []): HttpResponse
    {
        return $this->callWithMiddlewares(payment: $payment, uri: $uri, method: 'PUT', headers: $headers, params: $params);
    }

    /**
     * @param  string[]  $headers
     */
    public function delete(Payment $payment, string $uri, array $headers = []): HttpResponse
    {
        return $this->callWithMiddlewares(payment: $payment, uri: $uri, method: 'DELETE', headers: $headers);
    }

    /**
     * @param  string[]  $headers
     * @param  mixed[]  $params
     */
    public function callWithMiddlewares(Payment $payment, string $uri, string $method, array $headers, array $params = []): HttpResponse
    {

        $action = fn (HttpRequest $request): HttpResponse => $this->call(
            payment: $request->payment,
            uri: $request->uri,
            method: $request->method,
            headers: $request->headers,
            params: $request->params
        );

        foreach ($this->middlewares as $middleware) {

            if (is_string($middleware)) {
                $middleware = new $middleware();

            }

            if (! is_callable($middleware)) {
                throw new \Exception('Middleware is not callable');
            }

            $action = fn (HttpRequest $request): HttpResponse => $middleware($request, $action);
        }

        return $action(new HttpRequest(
            payment: $payment,
            uri: $uri,
            method: $method,
            headers: $headers,
            params: $params
        ));

    }

    public function addMiddleware(string|Closure $middleware): void
    {
        if (! in_array($middleware, $this->middlewares)) {
            $this->middlewares[] = $middleware;
        }
    }

    /**
     * @param  string[]  $headers
     * @param  mixed[]  $params
     */
    public function call(Payment $payment, string $uri, string $method, array $headers, array $params = []): HttpResponse
    {

        $url = $this->config->getApiUrl($uri);

        if (in_array($method, ['GET', 'DELETE'])) {
            $url .= '?'.http_build_query($params);
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'authorization: Bearer '.$this->config->secretKey,
                'x-auth-version: v1',
                'x-client-version: parapos-php:1.0',
                'x-auth-hash: '.$hash = uniqid('', true),
                'x-auth-signature: '.$this->config->signature($url, $hash),
                ...$headers,
            ],
        ];

        if (in_array($method, ['POST', 'PUT'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($params, JSON_THROW_ON_ERROR);
        }

        $request = curl_init();

        if ($request === false) {
            throw new \Exception('Failed to initialize curl');
        }

        curl_setopt_array($request, $options);

        $response = curl_exec($request);

        curl_close($request);

        if ($response === false) {
            throw new \Exception(curl_error($request), curl_errno($request));
        }

        if (! is_string($response)) {
            throw new \Exception('Response is not string');
        }

        return new HttpResponse(payment: $payment, response: $response, headers: curl_getinfo($request));

    }
}
