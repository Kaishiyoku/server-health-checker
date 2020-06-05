<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\DatabaseManager;

class StatusController extends Controller
{
    protected $db;

    protected $cache;

    /**
     * Create a new controller instance.
     *
     * @param DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function index()
    {
        dd(\Illuminate\Support\Str::random(32));

        /*** @var CacheRepository $cache */
        $cache = app()->make('cache.store');

        $health_checks = $cache->get('health_checks');

        return response()->json($health_checks);
    }
}
