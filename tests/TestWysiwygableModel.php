<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests;

use Brackets\AdminUI\Traits\HasWysiwygMediaTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 */
class TestWysiwygableModel extends Model
{
    use HasWysiwygMediaTrait;

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $table = 'test_wysiwygable_models';

    /**
     * @var array<string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = ['name'];
}
