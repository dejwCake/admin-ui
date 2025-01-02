<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'admin'])->group(function (): void {
    Route::namespace('Brackets\AdminUI\Http\Controllers')->group(function (): void {
        Route::post('/admin/wysiwyg-media','WysiwygMediaUploadController@upload')->name('brackets/admin-ui::wysiwyg-upload');
    });
});