<?php

namespace MBicknese\Portfolio\Routing;

use MBicknese\Portfolio\Api;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class Http
{

    public $routes;

    /**
     * Instantiates a new HTTP route handler
     * @param Api $api
     */
    public function __construct($api)
    {
        $this->activate();
    }

    public function activate()
    {
        $this->routes = new RouteCollection();
        Request::createFromGlobals();

    }
}
