<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Filesystem\Filesystem;

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

        $filesystem = app(Filesystem::class);
        static::deleted(static function ($model) use ($filesystem): void {
            $filesystem->delete(public_path() . '/' . $model->file_path);
        });
    }

    public function wysiwygable(): MorphTo
    {
        return $this->morphTo();
    }
}
