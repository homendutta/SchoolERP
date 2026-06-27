<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| The ERP front end (React) and the Flutter app consume a versioned API that
| each module provider registers. No business API endpoints are defined at the
| foundation stage. A liveness probe is exposed via withRouting(health: '/up').
*/

Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'status' => 'ok',
    'foundation' => 'Asylinx School ERP backend — engineering foundation',
]));
