<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Filesystem\Filesystem;

/**
 * @property int $id
 * @property string $file_path
 * @property int|null $wysiwygable_id
 * @property string|null $wysiwygable_type
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Model|null $wysiwygable
 */
final class WysiwygMedia extends Model
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
        self::deleted(static function ($model) use ($filesystem): void {
            $filesystem->delete(public_path() . '/' . $model->file_path);
        });
    }

    public function wysiwygable(): MorphTo
    {
        return $this->morphTo();
    }
}
