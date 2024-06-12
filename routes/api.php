<?php

use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\OrderController;
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
Route::controller(\App\Http\Controllers\Admin\AuthController::class)->group(function(){
    Route::post('/login', 'login');
});
Route::middleware('auth:web')->controller(\App\Http\Controllers\User\AuthController::class)->group(function(){
    Route::get('/logout', 'logout');
});
Route::middleware('auth:admin')->controller(\App\Http\Controllers\Admin\AuthController::class)->group(function(){
    Route::get('/admin/logout', 'logout');
    Route::put('/admin/edit/{id}', 'editAdmin');
    Route::put('/admin/reset/password/{id}', 'resetpassword');
});
// admin/*  routes for admin panel
Route::prefix('admin')->group(function(){
    Route::middleware('auth:admin')->group(function(){
        Route::prefix('category')->controller(\App\Http\Controllers\Admin\CategoryController::class)->group(function(){
            Route::get('/','index');
            Route::get('/get/{id}','get');
            Route::post('/store','store');
            Route::post('/update','update');
            Route::get('/delete/{id}','destroy');
            Route::put('/reorder/{id}','ordering');
        });
        Route::prefix('advertismant')->controller(\App\Http\Controllers\AdvertismentController::class)->group(function(){
            Route::get('/','index');
            Route::get('/get/{id}','show');
            Route::post('/store','store');
            Route::put('/update','update');
            Route::delete('/delete/{id}','destroy');
        });
        Route::prefix('contact')->controller(\App\Http\Controllers\Admin\ContactsController::class)->group(function(){
            Route::get('/','index');
            Route::get('/show/{id}','show');
            Route::post('/store','store');
            Route::put('/update/{id}','update');
            Route::delete('/delete/{id}','destroy');
        });
        Route::prefix('product')->group(function(){
            Route::controller(\App\Http\Controllers\Admin\ProductController::class)->group(function(){
                Route::get('/','index');
                Route::get('/{id}','get');
                Route::post('/store','store');
                Route::post('/update','update');
                Route::get('/delete/{id}','destroy');
                Route::put('/reorder/{id}','ordering');
                Route::put('/reorder/category/{id}','categoryOrdering');
            });

            Route::prefix('parts')->controller(\App\Http\Controllers\Admin\ProductPartController::class)->group(function(){
                Route::get('get/{product_id}','index');
                Route::get('/{part_id}','get');
                Route::post('/store','store');
                Route::post('/update','update');
                Route::get('/delete/{id}','destroy');
                Route::prefix('codes')->controller(\App\Http\Controllers\Admin\ProductPartCodeController::class)->group(function(){
                    Route::post('/','index');
                    Route::post('/store','store');
                    Route::post('/upload','uploadCodes');
                    Route::get('/delete/{id}','destroy');
                });
            });

        });
        Route::prefix('page')->controller(\App\Http\Controllers\Admin\PageController::class)->group(function(){
           Route::get('/','index');
           Route::get('/{id}','get');
           Route::post('/store','store');
           Route::post('/update','update');
           Route::get('/delete/{id}','destroy');
        });
        Route::prefix('currency')->controller(\App\Http\Controllers\Admin\CurrencyController::class)->group(function(){
            Route::get('/','index');
            Route::post('/store','store');
            Route::post('/update','update');
            Route::get('/delete/{id}','destroy');
        });
        Route::prefix('setting')->controller(\App\Http\Controllers\Admin\SettingController::class)->group(function(){
            Route::get('/','index');
            Route::post('update','update');
        });
        Route::prefix('social')->controller(\App\Http\Controllers\Admin\SocialController::class)->group(function(){
            Route::get('/','index');
            Route::post('store','store');
            Route::post('update','update');
            Route::get('delete/{id}','destroy');
        });
        Route::prefix('orders')->controller(\App\Http\Controllers\Admin\OrderController::class)->group(function(){
           Route::get('/','index');
           Route::get('/get/{id}','get');
           Route::post('delivery/code','delivery_code');
        });
        Route::prefix('slider')->controller(\App\Http\Controllers\Admin\SliderController::class)->group(function(){
           Route::get('/','index');
           Route::get('/{id}','show');
           Route::post('store','store');
           Route::post('update','update');
           Route::get('delete/{id}','destroy');
        });
        Route::prefix('payment/setting')->controller(\App\Http\Controllers\Admin\PaymentSettingController::class)->group(function(){
            Route::get('/','index');
            Route::post('store','store');
            Route::post('update','update');
            Route::get('delete/{id}','destroy');
        });
        Route::prefix('notification')->controller(\App\Http\Controllers\Admin\NotificationController::class)->group(function(){
            Route::get('/','index');
            Route::post('store','store');
            Route::post('update','update');
            Route::delete('delete/{id}','destroy');
        });
        Route::prefix('footer')->controller(\App\Http\Controllers\Admin\TableFooterController::class)->group(function(){
            Route::get('/','index');
            Route::post('/update','update');
        });
        Route::prefix('staticPage')->controller(\App\Http\Controllers\Admin\StaticPageController::class)->group(function(){
            Route::get('/','index');
            Route::get('/{id}','get');
            Route::post('/store','store');
            Route::post('/update','update');
        });
    });
});
Route::prefix('home')->controller(\App\Http\Controllers\User\HomeController::class)->group(function(){
    Route::get('products/popular','popular');
    Route::get('products/popular/paginate','popular_paginate');
    Route::get('products/search','search');
    Route::get('categories','category');
    Route::get('categories/{id}','getCategory');
    Route::get('products','product');
    Route::get('products/{id}','getProduct');
    Route::get('sliders','slider');
    Route::post('subscribe','subscribe');
    Route::get('staticPage','static_page');
    Route::get('staticPage/details/{id}','static_page_details');

});

Route::prefix('social')->controller(\App\Http\Controllers\Admin\SocialController::class)->group(function(){
    Route::get('/','index');
});
Route::prefix('footer')->controller(\App\Http\Controllers\Admin\TableFooterController::class)->group(function(){
    Route::get('/','index');
});
Route::prefix('contact')->controller(\App\Http\Controllers\Admin\ContactsController::class)->group(function(){
    Route::get('/','index');
});
Route::prefix('advertismant')->controller(\App\Http\Controllers\AdvertismentController::class)->group(function(){
    Route::get('/','index');
});
Route::prefix('pages')->controller(\App\Http\Controllers\Admin\PageController::class)->group(function(){
    Route::get('/{id}','get');
});

Route::prefix('user')->group(function(){
    Route::prefix('auth')->controller(\App\Http\Controllers\User\AuthController::class)->group(function(){
        Route::post('/login', 'login');
        Route::post('register','register');
        Route::post('/forget/password', 'forgetPassword');
        Route::middleware('auth:web')->post('/reset/password','resetpassword');
        Route::post('/social', 'socialLogin');
        Route::middleware('auth:web')->get('/', 'index');
        Route::middleware('auth:web')->post('/update', 'update');
    });
    Route::prefix('carts')->middleware('auth:web')->controller(\App\Http\Controllers\User\CartController::class)->group(function(){
        Route::get('/','index');
        Route::post('/store','store');
        Route::post('/update','update');
        Route::get('/delete/{id}','destroy');
    });
    Route::prefix('order')->middleware('auth:web')->controller(\App\Http\Controllers\User\OrderController::class)->group(function(){
       Route::get('/','index');
       Route::get('/get/{id}','get');
       Route::post('/checkout','store');
       Route::post('/direct','order_product');
       Route::post('/binance/pay','binance_pay');
       Route::get('/currancy','currancy');
       Route::post('/pay/currancy','pay_by_currancy');

    });
    Route::prefix('reviews')->middleware('auth:web')->controller(\App\Http\Controllers\User\ProductReviewController::class)->group(function(){
        Route::get('get/{id}','index');
        Route::post('/store','store');
    });
    Route::prefix('setting/data')->controller(\App\Http\Controllers\User\SettingController::class)->group(function(){
        Route::get('/','get_all_setting');
        Route::get('/payment','get_payment_setting');
    });
    Route::prefix('notification')->middleware('auth:web')->controller(\App\Http\Controllers\User\NotificationUserController::class)->group(function(){
        Route::get('/','index');
        Route::post('/read','markAsRead');
    });
});

Route::controller(\App\Http\Controllers\User\CheckoutController::class)->group(function(){
    Route::get('/checkout','checkout');
});

Route::controller(\App\Http\Controllers\User\OrderController::class)->group(function(){
    Route::post('/binancepay/callback','returnCallback');
    Route::post('/binancepay/cancel','cancelCallback');
});

