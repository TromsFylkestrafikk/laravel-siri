<?php

namespace TromsFylkestrafikk\Siri\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use TromsFylkestrafikk\Siri\Siri;

/**
 * Assert that incoming requests with version argument are supported by us.
 */
class SiriVersion
{
    /**
     * Assert version given as param is a valid siri version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = $request->route();
        if (!($route instanceof Route)) {
            return response("", Response::HTTP_NOT_FOUND);
        }
        $version = $route->parameter('version');
        if (!in_array($version, Siri::VERSIONS)) {
            return response(sprintf('SIRI version not supported: %s', $version), Response::HTTP_NOT_FOUND);
        }
        return $next($request);
    }
}
