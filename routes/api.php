<?php

use App\Actions\GetOriginalFile;
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
    if($request->all()['action'] !== 'created'){
        return ;
    }
    \App\Actions\ChangeFileWithGhComment::run($request->all()['comment']);
    //GetOriginalFile::run($request->all()['comment']);
});
