<?php

use MiniOrange\Classes\Actions\AuthFacadeController;

Route::get('mo_admin', function () {
    include_once 'launcher.php';
});

Route::get('register.php', function () {
    include_once 'register.php';
    include_once 'jsLoader.php';
    return view('mosaml::registerView');
});

Route::post('register.php', function () {
    include_once 'register.php';
    include_once 'jsLoader.php';
    return view('mosaml::registerView');
});

Route::get('admin_login.php', function () {
    include_once 'admin_login.php';
    include 'jsLoader.php';
    return view('mosaml::adminLoginView');
});

Route::post('admin_login.php', function () {
    include_once 'admin_login.php';
    include 'jsLoader.php';
    return view('mosaml::adminLoginView');
});

Route::get('login.php/{RelayState?}', function ($RelayState = '/') {

    include_once 'login.php';
});
Route::get('licensing.php', function () {
    include_once 'licensing.php';
    include 'jsLoader.php';
    return view('mosaml::licensingView');
});
Route::post('licensing.php', function () {
    include_once 'licensing.php';
    include 'jsLoader.php';
    return view('mosaml::licensingView');
});

Route::get('setup.php', function () {
    include_once 'setup.php';
    include_once 'jsLoader.php';
    return view('mosaml::setupView');
});

Route::post('sso.php', function () {
    include_once 'sso.php';
});
Route::post('', function () {
    include_once 'sso.php';
});
Route::get('admin_logout.php', function () {
    include_once 'admin_logout.php';
});

Route::get('how_to_setup.php', function () {
    include_once 'how_to_setup.php';
    include_once 'jsLoader.php';
    return view('mosaml::howToSetupView');
});

Route::get('support.php', function () {
    include_once 'support.php';
    include_once 'jsLoader.php';
    return view('mosaml::supportView');
});
Route::post('support.php', function () {
    include_once 'support.php';
    include_once 'jsLoader.php';
    return view('mosaml::supportView');
});


Route::post('setup.php', function () {
    include_once 'setup.php';
    include_once 'jsLoader.php';
    return view('mosaml::setupView');
});

Route::post('how_to_setup.php', function () {
    include_once 'how_to_setup.php';
    return view('mosaml::howToSetupView');
});

Route::get('sign/{email?}', 'MiniOrange\Classes\Actions\AuthFacadeController@signin');
Route::get('create_tables', 'MiniOrange\Classes\Actions\DatabaseController@createTables');


