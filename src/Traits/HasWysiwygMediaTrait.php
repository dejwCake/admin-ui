<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Traits;

use Brackets\AdminUI\Models\WysiwygMedia;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasWysiwygMediaTrait
{
    public static function bootHasWysiwygMediaTrait(): void
    {
        static::saved(static function ($model): void {
            $wysiwygMediaIds = (new Collection(request('wysiwygMedia')))->filter(
                static fn ($wysiwygId) => is_int($wysiwygId),
            );
            if ($wysiwygMediaIds->isNotEmpty()) {
                WysiwygMedia::whereIn('id', $wysiwygMediaIds)->get()->each(static function ($item) use ($model): void {
                    $model->wysiwygMedia()->save($item);
                });
            }
        });

        static::deleted(static function ($model): void {
            $model->wysiwygMedia->each(static function ($item): void {
                $item->delete();
            });
        });
    }

    public function wysiwygMedia(): MorphMany
    {
        return $this->morphMany(WysiwygMedia::class, 'wysiwygable');
    }
}
