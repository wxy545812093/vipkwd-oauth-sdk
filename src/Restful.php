<?php

declare(strict_types=1);

namespace Vipkwd\OAuth;

use Vipkwd\OAuth\OAuth as VKOauth;

class Restful
{
    static function get(string $service, int $resourceId, array $data = [])
    {
        return (new VKOauth([]))->resource('get', rtrim($service, '/') . '/' . $resourceId, $data);
    }

    static function put(string $service, int $resourceId, array $data = [])
    {
        return (new VKOauth([]))->resource('put', rtrim($service, '/') . '/' . $resourceId, $data);
    }
    static function post(string $service, int $resourceId, array $data = [])
    {
        return (new VKOauth([]))->resource('post', rtrim($service, '/') . '/' . $resourceId, $data);
    }
    static function delete(string $service, int $resourceId, array $data = [])
    {
        return (new VKOauth([]))->resource('delete', rtrim($service, '/') . '/' . $resourceId, $data);
    }
}
