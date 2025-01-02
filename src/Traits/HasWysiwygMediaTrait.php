<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Traits;

use Brackets\AdminUI\WysiwygMedia;
use Illuminate\Support\Collection;

trait HasWysiwygMediaTrait {

    public static function bootHasWysiwygMediaTrait(): void
    {
        static::saved(function ($model) {
            $wysiwygMediaIds = (new Collection(request('wysiwygMedia')))->filter(function($wysiwygId){
                return is_int($wysiwygId);
            });
            if($wysiwygMediaIds->isNotEmpty()) {
                WysiwygMedia::whereIn('id', $wysiwygMediaIds)->get()->each(function($item) use ($model) {
                    $model->wysiwygMedia()->save($item);
                });
            }
        });

        static::deleted(function($model) {
            $model->wysiwygMedia->each(function($item){
                $item->delete();
            });
        });
    }

    public function wysiwygMedia()
    {
        return $this->morphMany('Brackets\AdminUI\WysiwygMedia', 'wysiwygable');
    }
}