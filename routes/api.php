<?php

use App\Http\Controllers\api\CategoryController as ApiCategoryController;
use App\Http\Controllers\api\ProductController as ApiProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('auth');
        return [
            'message' => 'login was successful',
            'data'    => [
                'token' => $token->plainTextToken,
            ]
        ];
    }

    return [
        'message' => 'email or password is wrong'
    ];
});

Route::resource('products', ProductController::class)/*->middleware('auth:sanctum')*/;
Route::resource('categories', CategoryController::class)/*->middleware('auth:sanctum')*/;
Route::apiResource('api-products', ApiProductController::class)/*->middleware('auth:sanctum')*/;
Route::apiResource('api-categories', ApiCategoryController::class)->only(['index', 'show']);

