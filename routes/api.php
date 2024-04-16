<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('unauth', function () {
    return response()->json([
        'status' => false,
        'message' => __('translate.unauthorized'),
    ]);
})->name('login');

// admin/*  routes for admin panel
Route::prefix('admin')->group(function(){
    Route::prefix('auth')->controller(App\Http\Controllers\Admin\AuthController::class)->group(function(){
        Route::post('/login', 'login');
    });
    Route::middleware('auth:admin')->group(function(){
        Route::prefix('category')->controller(App\Http\Controllers\Admin\CategoryController::class)->group(function(){
            Route::get('/','index');
            Route::post('/store','store');
            Route::post('/update','update');
            Route::get('/delete/{id}','destroy');
        });
        Route::prefix('products')->controller(App\Http\Controllers\Admin\ProductController::class)->group(function(){
            Route::get('/','index');
            Route::post('/store','store');
            Route::post('/update','update');
            Route::get('/delete/{id}','destroy');
        });
        Route::prefix('page')->controller(App\Http\Controllers\Admin\PageController::class)->group(function(){
           Route::get('/','index');
           Route::post('/store','store');
           Route::post('/update','update');
           Route::get('/delete/{id}','destroy');
        });
        Route::prefix('currency')->controller(App\Http\Controllers\Admin\CurrencyController::class)->group(function(){
            Route::get('/','index');
            Route::post('/store','store');
            Route::post('/update','update');
            Route::get('/delete/{id}','destroy');
        });
        Route::prefix('setting')->controller(App\Http\Controllers\Admin\SettingController::class)->group(function(){
            Route::get('/','index');
            Route::post('update','update');
        });
        Route::prefix('social')->controller(App\Http\Controllers\Admin\SocialController::class)->group(function(){
            Route::get('/','index');
            Route::post('store','store');
            Route::post('update','update');
            Route::get('delete/{id}','destroy');
        });
        Route::prefix('orders')->controller(App\Http\Controllers\Admin\OrderController::class)->group(function(){
           Route::get('/','index');
           Route::post('delivery/code','delivery_code');
        });
        Route::prefix('slider')->controller(App\Http\Controllers\Admin\SliderController::class)->group(function(){
           Route::get('/','index');
           Route::post('store','store');
           Route::post('update','update');
           Route::get('delete/{id}','destroy');
        });
    });
});

Route::prefix('home')->controller(App\Http\Controllers\User\HomeController::class)->group(function(){
    Route::get('products/popular','popular');
    Route::get('categories','category');
    Route::get('products','product');
    Route::get('sliders','slider');
    Route::post('subscribe','subscribe');
});


Route::prefix('user')->group(function(){
    Route::prefix('auth')->controller(App\Http\Controllers\User\AuthController::class)->group(function(){
        Route::post('/login', 'login');
        Route::post('register','register');
        Route::post('/reset/password','resetpassword');
    });
    Route::prefix('carts')->middleware('auth:web')->controller(App\Http\Controllers\User\CartController::class)->group(function(){
        Route::get('/','index');
        Route::post('/store','store');
        Route::post('/update','update');
        Route::get('/delete/{id}','destroy');
    });
    Route::prefix('order')->middleware('auth:web')->controller(App\Http\Controllers\User\OrderController::class)->group(function(){
       Route::get('/','index');
       Route::post('/checkout','store');
    });
});




