<?php

// Password reset routes
Route::get('/password/reset-request', 'App\Http\Controllers\Auth\PasswordResetOtpController@showResetRequestForm')->name('password.reset.request.form');
Route::post('/password/reset-request', 'App\Http\Controllers\Auth\PasswordResetOtpController@requestPasswordReset')->name('password.reset.request');

Route::get('/password/verify-otp', 'App\Http\Controllers\Auth\PasswordResetOtpController@showVerifyOtpForm')->name('password.verify.otp.form');
Route::post('/password/verify-otp', 'App\Http\Controllers\Auth\PasswordResetOtpController@verifyOtp')->name('password.verify.otp');

Route::get('/password/update', 'App\Http\Controllers\Auth\PasswordResetOtpController@showUpdatePasswordForm')->name('password.update.form');
Route::post('/password/update', 'App\Http\Controllers\Auth\PasswordResetOtpController@updatePasswordWithOtp')->name('password.update.withotp');