<?php
namespace Dot\Crud\System\Middleware\Base;

use Dot\Crud\System\Request;

interface IHandler  {

    public function handle(Request $request);

}