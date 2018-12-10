<?php
namespace Dot\Crud\System\Cache;

use Dot\Crud\System\Config;
use Dot\Crud\Running\Cache\NoCache;

class CacheFactory  {

    const PREFIX                = 'evie-rest-%s-';

    private static function getPrefix() {
        return sprintf(self::PREFIX, substr(md5(__FILE__), 0, 8));
    }

    public static function create(Config $config)   {
        switch ($config->getCacheType()) {
            default:
                $cache = new NoCache();
        }

        return $cache;
    }

}