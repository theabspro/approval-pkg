<?php
Route::group(['namespace' => 'Abs\ApprovalPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'approval-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});