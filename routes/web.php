<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->post('/login', 'AuthController@login');
$router->get('/me', ['middleware' => 'auth:api', 'uses' => 'AuthController@me']);
$router->post('/register', 'AuthController@register');
$router->post('/registerupdate', 'AuthController@updateRegister');
$router->post('/refresh', 'AuthController@refresh');
$router->post('/request-otp', 'AuthController@requestOtp');
$router->post('/check-otp', 'AuthController@checkOtp');
$router->post('/reset-password', 'AuthController@resetPassword');


$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->post('/logout', 'AuthController@logout');
    $router->post('list-by-role', 'MenuRoleController@listByRole');
});

// Group with JWT middleware
$router->group(['prefix' => 'api/users', 'middleware' => 'auth:api'], function () use ($router) {
    // Create user
    $router->post('create', 'UserController@create');

    // View all users
    $router->post('list', 'UserController@list');

    // View single user by ID
    $router->post('detail', 'UserController@detail');

    // Update user
    $router->post('update', 'UserController@update');

    // Delete user
    $router->post('delete', 'UserController@delete');


});

$router->group(['prefix' => 'api/pekerjaan', 'middleware' => 'auth:api'], function () use ($router) {
    $router->post('create', 'PekerjaanMasterController@create');
    $router->post('list',   'PekerjaanMasterController@list');
    $router->post('detail', 'PekerjaanMasterController@detail');
    $router->post('update', 'PekerjaanMasterController@update');
    $router->post('delete', 'PekerjaanMasterController@delete');
});

$router->group(['prefix' => 'api/provinsi', 'middleware' => 'auth:api'], function () use ($router) {
    $router->post('create', 'ProvinsiMasterController@create');
    $router->post('list',   'ProvinsiMasterController@list');
    $router->post('detail', 'ProvinsiMasterController@detail');
    $router->post('update', 'ProvinsiMasterController@update');
    $router->post('delete', 'ProvinsiMasterController@delete');
});

$router->group(['prefix' => 'api/kabupaten', 'middleware' => 'auth:api'], function () use ($router) {
    $router->post('create', 'KabupatenMasterController@create');
    $router->post('list',   'KabupatenMasterController@list');
    $router->post('detail', 'KabupatenMasterController@detail');
    $router->post('update', 'KabupatenMasterController@update');
    $router->post('delete', 'KabupatenMasterController@delete');
});

$router->group(['prefix' => 'api/kecamatan', 'middleware' => 'auth:api'], function () use ($router) {
    $router->post('create', 'KecamatanMasterController@create');
    $router->post('list',   'KecamatanMasterController@list');
    $router->post('detail', 'KecamatanMasterController@detail');
    $router->post('update', 'KecamatanMasterController@update');
    $router->post('delete', 'KecamatanMasterController@delete');
});

$router->group(['prefix' => 'api/kelurahan', 'middleware' => 'auth:api'], function () use ($router) {
    $router->post('create', 'KelurahanMasterController@create');
    $router->post('list',   'KelurahanMasterController@list');
    $router->post('detail', 'KelurahanMasterController@detail');
    $router->post('update', 'KelurahanMasterController@update');
    $router->post('delete', 'KelurahanMasterController@delete');
});

$router->group(['prefix' => 'api/profiles'], function () use ($router) {
    $router->post('create', 'ProfilesController@create');
    $router->post('list',   'ProfilesController@list');
    $router->post('detail', 'ProfilesController@detail');
    $router->post('update', 'ProfilesController@update');
    $router->post('delete', 'ProfilesController@delete');
});

$router->group(['prefix' => 'api/lov'], function () use ($router) {
    $router->post('list_kelurahan',   'KelurahanMasterController@list');
    $router->post('list_kecamatan',   'KecamatanMasterController@list');
    $router->post('list_kabupaten',   'KabupatenMasterController@list');
    $router->post('list_provinsi',   'ProvinsiMasterController@list');
    $router->post('list_pekerjaan',   'PekerjaanMasterController@list');
});







$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->get('/tes', function () {
    return response()->json(['message' => 'Lumen OK!']);
});

