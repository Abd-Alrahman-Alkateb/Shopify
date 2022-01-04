<?php

use App\Http\Controllers\api\CategoryController as ApiCategoryController;
use App\Http\Controllers\api\ProductController as ApiProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


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

Route::post('/register', function (Request $request) {
    $validation = $request->validate([
        'fname'    => 'required',
        'lname'    => 'required',
        'email'    => 'required|email',
        'password'    => 'required',
    ]);

    $validation['password'] = bcrypt($validation['password']);
    $user = User::create($validation);
    $token = $user->createToken('auth');
        return [
            'message' => 'User successfully registered!',
            'data'    => [
                'name'  => $user->fname,
                'token' => $token->plainTextToken
            ]
        ];
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
                'name'  => $user->fname,
                'token' => $token->plainTextToken
            ]
        ];
    }

    return [
        'message' => 'email or password is wrong'
    ];
});

Route::post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    // Auth::user()->tokens()->delete();

    return [
        'message' => 'You have successfully logged out and the token was successfully deleted'
    ];
})->middleware('auth:sanctum');


Route::Get('my-products',[ApiProductController::class,'myProducts'])->middleware('auth:sanctum');
Route::post('search',[ApiProductController::class,'search'])->middleware('auth:sanctum');
Route::apiResource('api-categories', ApiCategoryController::class)->middleware('auth:sanctum');
Route::apiResource('api-products', ApiProductController::class)->middleware('auth:sanctum');

