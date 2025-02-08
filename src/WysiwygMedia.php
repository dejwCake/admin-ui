<?php

declare(strict_types=1);

namespace Brackets\AdminUI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\File;

class WysiwygMedia extends Model
{
    /**
     * @var array<string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = [
        'file_path',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(static function ($model): void {
            File::delete(public_path() . '/' . $model->file_path);
        });
    }

    public function wysiwygable(): MorphTo
    {
        return $this->morphTo();
    }
}
