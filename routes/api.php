<?php

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
Route::post('user/login', 'API\UserController@login');

Route::middleware('ApiToken')->group(function () {

    Route::post('user/detail', 'API\UserController@details');
    /////////////////////////////// Roles //////////////////////////////////////////////
    Route::post('roles/index', 'API\UserManagement\RolesController@index');
    Route::post('roles/getallroles', 'API\UserManagement\RolesController@getallroles');
    Route::post('role/store', 'API\UserManagement\RolesController@store');
    Route::post('role/edit', 'API\UserManagement\RolesController@edit');
    Route::post('role/update', 'API\UserManagement\RolesController@update');
    Route::delete('role/delete', 'API\UserManagement\RolesController@destroy');
    Route::post('role/change_status', 'API\UserManagement\RolesController@changestatus');

    Route::post('permissions/index', 'API\UserManagement\PermissionsController@index');
    ////////////////////////////// Users ///////////////////////////////////////////////////
    Route::post('users/index', 'API\UserManagement\UsersController@index');
    Route::post('users/getallusers', 'API\UserManagement\UsersController@getusers');
    Route::post('user/store', 'API\UserManagement\UsersController@store');
    Route::post('user/edit', 'API\UserManagement\UsersController@edit');
    Route::post('user/update', 'API\UserManagement\UsersController@update');
    Route::delete('user/delete', 'API\UserManagement\UsersController@destroy');
    Route::post('user/change_status', 'API\UserManagement\UsersController@changestatus');
    Route::post('user/change_permissions', 'API\UserManagement\UsersController@changepermissions');

    Route::post('user/changepassword', 'API\UserController@changepassword');

    //////////////////////////////// Grades /////////////////////////////////////////////////
    Route::post('grades/index', 'API\GradeController@index');
    Route::post('grade/store', 'API\GradeController@store');
    Route::post('grade/edit', 'API\GradeController@edit');
    Route::post('grade/update', 'API\GradeController@update');
    Route::post('grade/delete', 'API\GradeController@destroy');
    Route::post('grade/change_status', 'API\GradeController@change_status');

    //////////////////////////////// Classes /////////////////////////////////////////////////
    Route::post('classes/index', 'API\ClassroomController@index');
    Route::post('class/store', 'API\ClassroomController@store');
    Route::post('class/edit', 'API\ClassroomController@edit');
    Route::post('class/update', 'API\ClassroomController@update');
    Route::post('class/delete', 'API\ClassroomController@destroy');
    Route::post('class/change_status', 'API\ClassroomController@change_status');
});

