<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * On environments like Google Cloud Run, all traffic comes from a trusted
     * proxy, so we can trust all of them.
     *
     * @var array|string|null
     */
    protected $proxies = '*'; // <--- THIS IS THE CHANGE

    /**
     * The headers that should be used to detect proxies.
     *
     * This tells Laravel to look for all the standard `X-Forwarded-*` headers.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL; // A good modern default
}
