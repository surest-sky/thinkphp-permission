<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-28
 * Time: 下午2:05
 */

namespace Surest\Middleware;


class SAuthMiddleware
{
    public function handle($request, \Closure $next)
    {

        return $next($request);
    }
}