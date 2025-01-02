<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'admin'])->group(static function (): void {
    Route::namespace('Brackets\AdminUI\Http\Controllers')->group(static function (): void {
        Route::post('/admin/wysiwyg-media', 'WysiwygMediaUploadController@upload')->name(
            'brackets/admin-ui::wysiwyg-upload',
        );
    });
});
