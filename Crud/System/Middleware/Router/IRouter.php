<?php
namespace Dot\Crud\System\Middleware\Router;

use Dot\Crud\System\Middleware\Base\IHandler;
use Dot\Crud\System\Middleware\Base\Middleware;
use Dot\Crud\System\Request;

interface IRouter extends IHandler   {

    public function register($method = null, $path = null, array $handler = []);

    public function load(Middleware $middleware = null);

    public function route(Request $request = null);

}