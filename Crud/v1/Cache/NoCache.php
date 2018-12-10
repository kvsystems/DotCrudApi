<?php
namespace Dot\Crud\Running\Cache;

use Dot\Crud\System\Cache\ICache;

class NoCache implements ICache {

    public function __construct(){}

    public function set($key = null, $value = null, $ttl = 0)   {
        return true;
    }

    public function get($key = null)    {
        return '';
    }

    public function clear() {
        return true;
    }

}