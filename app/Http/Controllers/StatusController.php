<?php

namespace App\Http\Controllers;

use App\Console\Commands\RunHealthChecks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class StatusController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $cacheValue = Cache::get('health_checks');

        if (!$cacheValue){
            Artisan::call(RunHealthChecks::class);

            $cacheValue = Cache::get('health_checks');
        }

        return response()->json($cacheValue);
    }
}
