<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Config;

abstract class Service
{
    public Http $http;

    final public function __construct(public Config $config, Http $http = null)
    {
        $this->http = $http ?? new Http($this->config);
    }

    /**
     * @param  string[]|string|\Closure  $middlewares
     */
    public function middleware(array|string|\Closure $middlewares = []): self
    {
        if (! is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        foreach ($middlewares as $middleware) {
            $this->http->addMiddleware($middleware);
        }

        return $this;
    }
}
