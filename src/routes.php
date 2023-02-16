<?php
$prefix = config('subscriber.prefix', '');

Route::group(['prefix' => $prefix, 'namespace' => '\Megaads\EmailSubscriber\Controllers'], function() {
    Route::any('activity/subscribe', 'SubscriberController@subscribeEmail');
    Route::any('activity/unsubscribe', 'SubscriberController@unsubscribeEmail');
});

Route::group([
    'prefix' => $prefix ? $prefix . '/' : 'email-subs/api/',
    'namespace' => '\Megaads\EmailSubscriber\Controllers',
    'middleware' => ['subs_auth']
], function() {
    Route::get('config', 'SubscriberController@readPackageConfig');
    Route::post('config', 'SubscriberController@savePackageConfig');
});
