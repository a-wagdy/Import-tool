<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use \App\Models\Employee;
Route::get('/', function () {

    dd(Employee::setDateAsValidDateTime('9/16/1992'));
    dd(\App\Enums\Gender::M->value);

    //\App\Models\Employee::factory()->count(5)->create();

//    $employee = Employee::with('addresses')->find(6);
//
//    $employee->addresses()->create([
//        'place_name' => 'fhfhhsad',
//        'country' => 'Canada',
//        'city' => 'Quebec',
//        'region' => 'unknown',
//        'zip' => 213123
//    ]);
//
//    $employee->refresh();
//    dd($employee->toArray());

    return view('welcome');
});
