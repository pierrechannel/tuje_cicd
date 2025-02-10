<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;

class CacheController extends Controller
{
    /**
     * Clear application cache, route cache, and view cache.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return response()->json(['message' => 'All caches cleared successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error clearing cache: ' . $e->getMessage()], 500);
        }
    }
}

