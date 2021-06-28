<?php

Route::get('mo_admin', 'MiniOrange\Classes\Actions\MoAdminController@launch');

Route::get('register.php', 'MiniOrange\Classes\Actions\MoRegisterController@launch');
Route::post('register.php', 'MiniOrange\Classes\Actions\MoRegisterController@launch');

Route::get('admin_login.php', 'MiniOrange\Classes\Actions\MoAdminLoginController@launch');
Route::post('admin_login.php', 'MiniOrange\Classes\Actions\MoAdminLoginController@launch');

Route::get('login.php/{RelayState?}', 'MiniOrange\Classes\Actions\MoRelayStateController@launch');

Route::get('licensing.php', 'MiniOrange\Classes\Actions\MoLicensingController@launch');
Route::post('licensing.php', 'MiniOrange\Classes\Actions\MoLicensingController@launch');

Route::get('setup.php', 'MiniOrange\Classes\Actions\MoSetupController@launch');
Route::post('setup.php', 'MiniOrange\Classes\Actions\MoSetupController@launch');

Route::post('sso.php', 'MiniOrange\Classes\Actions\MoSSOController@launch');
Route::post('', 'MiniOrange\Classes\Actions\MoSSOController@launch');

Route::get('admin_logout.php', 'MiniOrange\Classes\Actions\MoAdminLogoutController@launch');

Route::get('how_to_setup.php', 'MiniOrange\Classes\Actions\MoHowToSetupController@launch');
Route::post('how_to_setup.php', 'MiniOrange\Classes\Actions\MoHowToSetupController@launch');

Route::get('support.php', 'MiniOrange\Classes\Actions\MoSupportController@launch');
Route::post('support.php', 'MiniOrange\Classes\Actions\MoSupportController@launch');

Route::get('sign/{email?}', 'MiniOrange\Classes\Actions\AuthFacadeController@signin');
Route::get('create_tables', 'MiniOrange\Classes\Actions\DatabaseController@createTables');