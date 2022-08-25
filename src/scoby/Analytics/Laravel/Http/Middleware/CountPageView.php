<?php namespace Scoby\Analytics\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Scoby\Analytics\Client;

class CountPageView
{
    private Client $client;

    /**
     * @throws \Exception
     */
    public function __construct(Log $logger)
    {
        $jarId = env('SCOBY_JAR_ID');
        if(!$jarId) {
            throw new \Exception('Cannot initialize scoby analytics without $jarId. Please set env variable SCOBY_JAR_ID');
        }
        $this->client = new Client($jarId);
        $this->client->setLogger($logger::getLogger());
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->format() === "html" && $request->method() === "GET") {
            $this->client->logPageViewAsync();
            Log::info("queued page view for async logging");
        }
        return $next($request);
    }
}
