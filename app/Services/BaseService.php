<?php

namespace App\Services;

use App\Infrastructure\Cache\CacheManager;

abstract class BaseService
{
    protected CacheManager $cache;

    public function __construct()
    {
        $this->cache = CacheManager::getInstance();
    }
}
