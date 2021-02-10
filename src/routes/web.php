<?php

Route::group(['namespace' => 'Abs\ApprovalPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'Approval-pkg'], function () {
	Route::get('/approval-type/get-list', 'ApprovalTypeController@getApprovalTypeList')->name('getApprovalTypeList');
	Route::get('/approval-type/get-form-data', 'ApprovalTypeController@getApprovalTypeFormData')->name('getApprovalTypeFormData');
	Route::post('/approval-type/save', 'ApprovalTypeController@saveApprovalType')->name('saveApprovalType');
	Route::get('/approval-type/delete', 'ApprovalTypeController@deleteApprovalType')->name('deleteApprovalType');
	Route::get('approval-type/view', 'ApprovalTypeController@viewApprovalType')->name('viewApprovalType');
	Route::post('/approval-type-level/save', 'ApprovalTypeController@saveApprovalTypeLevel')->name('saveApprovalTypeLevel');
	Route::get('/approval-status', 'ApprovalTypeController@getApprovalStatus')->name('getApprovalStatus');
	Route::post('/approval-type/approval-level-list', 'ApprovalTypeController@getApprovalLevelsList')->name('getApprovalLevelsList');

	//Approval Level
	Route::get('/approval-level/get-list', 'ApprovalLevelController@getApprovalLevelList')->name('getApprovalLevelList');
	Route::get('/approval-level/get-form-data', 'ApprovalLevelController@getApprovalLevelFormData')->name('getApprovalLevelFormData');
	Route::post('/approval-level/save', 'ApprovalLevelController@saveApprovalLevel')->name('saveApprovalLevel');
	Route::get('/approval-level/delete', 'ApprovalLevelController@deleteApprovalLevel')->name('deleteApprovalLevel');
	Route::get('/approval-level/filter', 'ApprovalLevelController@getApprovalLevelFilter')->name('getApprovalLevelFilter');
	//Getting mail drop down list by Karthick T on 10-02-2021
	Route::get('/approval-level/get-mail-data', 'ApprovalLevelController@getMailData')->name('getMailData');

	//Entity Statuses
	Route::get('/entity-status/get-list', 'EntityStatusController@getEntityStatusList')->name('getEntityStatusList');
	Route::get('/entity-status/get-form-data', 'EntityStatusController@getEntityStatusFormData')->name('getEntityStatusFormData');
	Route::post('/entity-status/save', 'EntityStatusController@saveEntityStatus')->name('saveEntityStatus');
	Route::get('/entity-status/delete', 'EntityStatusController@deleteEntityStatus')->name('deleteEntityStatus');
	Route::get('/entity-status/filter', 'EntityStatusController@getEntityStatusFilter')->name('getEntityStatusFilter');

	//Approval Flow Configuration
	Route::get('/approval-flow-configuration/get-list', 'ApprovalFlowConfigurationController@getApprovalFlowConfigurationList')->name('getApprovalFlowConfigurationList');
	Route::get('/approval-flow-configuration/get-form-data', 'ApprovalFlowConfigurationController@getApprovalFlowConfigurationFormData')->name('getApprovalFlowConfigurationFormData');
	Route::post('/approval-flow-configuration/save', 'ApprovalFlowConfigurationController@saveApprovalFlowConfiguration')->name('saveApprovalFlowConfiguration');
	Route::get('/approval-flow-configuration/delete', 'ApprovalFlowConfigurationController@deleteApprovalFlowConfiguration')->name('deleteApprovalFlowConfiguration');
	Route::get('/approval-flow-configuration/filter', 'ApprovalFlowConfigurationController@getApprovalFlowConfigurationFilter')->name('getApprovalFlowConfigurationFilter');
});