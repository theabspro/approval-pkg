<?php

Route::group(['namespace' => 'Abs\ApprovalPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'Approval-pkg'], function () {
	Route::get('/approval-level/get-list', 'ApprovalLevelController@getApprovalLevelList')->name('getApprovalLevelList');
	Route::get('/approval-leve/get-form-data/{id?}', 'ApprovalLevelController@getApprovalLevelFormData')->name('getApprovalLevelFormData');
	Route::post('/approval-leve/save', 'ApprovalLevelController@saveApprovalLevel')->name('saveApprovalLevel');
	Route::get('/approval-leve/delete/{id}', 'ApprovalLevelController@deleteCustomer')->name('deleteApprovalLevel');

});