<?php

use App\Mail\TestSendHostMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('send', function () {
    Mail::to('a.mansour.code@gmail.com')->send(new TestSendHostMail);
    return 'send';
});