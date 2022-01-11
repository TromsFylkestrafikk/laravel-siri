<?php

namespace TromsFylkestrafikk\Siri\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;

class SubscribedChannel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var SiriSubscription $subscription */
        $subscription = $request->route('subscription');
        if (strtolower($subscription->channel) !== strtolower($request->route('channel'))) {
            return response('Wrong channel', Response::HTTP_CONFLICT);
        }
        return $next($request);
    }
}
