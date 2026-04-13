<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/dashboard/investor', function () {
    return view('dashboards.investor');
});

Route::get('/dashboard/admin', function () {
    return view('dashboards.admin');
});

Route::get('/dashboard/auditor', function () {
    return view('dashboards.auditor');
});
