<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/foo', function () {
    return response()
        ->json([
            'foo' => 'bar',
        ]);
});

Route::post('/github-webhook', function (Request $request) {
    if ($request->all()['action'] !== 'created') {
        return;
    }
    if (explode(' ', $request->all()['comment']['body'])[0] !== '/gen') {
        logger('hello');

        return;
    }
    \App\Actions\ChangeFileWithGhComment::run(
        $request->all()['comment']['body'],
        $request->all()['comment']['path'],
        $request->all()['comment']['line'],
        $request->all()['pull_request']['head']['repo']['name'],
        $request->all()['pull_request']['head']['repo']['owner']['login'],
        $request->all()['pull_request']['head']['ref'],
        $request->all()['comment']['start_line'],
    );
});
