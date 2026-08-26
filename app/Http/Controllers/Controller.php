<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected array $middleware = [];

    public function middleware($middleware, array $options = []): ControllerMiddlewareOptions
    {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = ['middleware' => $m, 'options' => &$options];
        }

        return new ControllerMiddlewareOptions($options);
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}

class ControllerMiddlewareOptions
{
    public function __construct(protected array &$options) {}

    public function only(array|string $methods): static
    {
        $this->options['only'] = (array) $methods;
        return $this;
    }

    public function except(array|string $methods): static
    {
        $this->options['except'] = (array) $methods;
        return $this;
    }
}
