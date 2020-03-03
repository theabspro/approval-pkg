<?php

Route::group(['namespace' => 'Abs\ApprovalPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'Approval-pkg'], function () {
	Route::get('/approval-type/get-list', 'ApprovalTypeController@getApprovalTypeList')->name('getApprovalTypeList');
	Route::get('/approval-type/get-form-data', 'ApprovalTypeController@getApprovalTypeFormData')->name('getApprovalTypeFormData');
	Route::post('/approval-type/save', 'ApprovalTypeController@saveApprovalType')->name('saveApprovalType');
	Route::get('/approval-type/delete', 'ApprovalTypeController@deleteApprovalType')->name('deleteApprovalType');
	Route::get('approval-type/view', 'ApprovalTypeController@viewApprovalType')->name('viewApprovalType');
	Route::post('/approval-level/save', 'ApprovalTypeController@saveApprovalLevel')->name('saveApprovalLevel');
	Route::get('/approval-status', 'ApprovalTypeController@getApprovalStatus')->name('getApprovalStatus');
});