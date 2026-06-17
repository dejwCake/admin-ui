<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Models;

use Brackets\AdminUI\Observers\WysiwygMediaObserver;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $file_path
 * @property int|null $wysiwygable_id
 * @property string|null $wysiwygable_type
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Model|null $wysiwygable
 */
#[Fillable(['file_path'])]
#[ObservedBy(WysiwygMediaObserver::class)]
final class WysiwygMedia extends Model
{
    public function wysiwygable(): MorphTo
    {
        return $this->morphTo();
    }
}
