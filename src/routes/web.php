<?php

Route::group(['namespace' => 'Abs\ApprovalPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'Approval-pkg'], function () {
	Route::get('/approval-type/get-list', 'ApprovalTypeController@getApprovalTypeList')->name('getApprovalTypeList');
	Route::get('/approval-type/get-form-data/{id?}', 'ApprovalTypeController@getApprovalTypeFormData')->name('getApprovalTypeFormData');
	Route::post('/approval-type/save', 'ApprovalTypeController@saveApprovalType')->name('saveApprovalType');
	Route::get('/approval-type/delete/{id}', 'ApprovalTypeController@deleteApprovalType')->name('deleteApprovalType');

});