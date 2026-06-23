<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/docs', function () {
    return redirect('/docs/index.html');
});

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (! in_array($locale, ['en', 'id'], true)) {
        abort(404);
    }

    $request->session()->put('locale', $locale);

    return redirect()->back();
})->name('locale.switch');
