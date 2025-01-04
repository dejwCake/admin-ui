<?php

declare(strict_types=1);

use Brackets\AdminUI\Http\Controllers\WysiwygMediaUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'admin'])
    ->prefix('admin')
    ->group(static function (): void {
        Route::post('/wysiwyg-media', [WysiwygMediaUploadController::class, 'upload'])
            ->name('brackets/admin-ui::wysiwyg-upload');
    });
