<?php

declare(strict_types=1);

namespace Vipkwd\OAuth;

use Vipkwd\OAuth\OAuth as VKOauth;

class Restful
{
    public static function __callStatic(string $method, array $arguments)
    {
        $method = strtolower($method);
        $arguments[0] = (string)($arguments[0]);
        $arguments[1] = (int)($arguments[1] ?? 0);
        $arguments[2] = (array)($arguments[2] ?? []);

        if (in_array($method, ['post', 'delete', 'put', 'get'])) {
            return (new VKOauth([]))->resource($method, rtrim($arguments[0], '/') . '/' . $arguments[1], $arguments[2]);
        }
        return null;
    }
}
