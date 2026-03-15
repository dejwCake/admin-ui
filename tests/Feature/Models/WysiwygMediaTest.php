<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature\Models;

use Brackets\AdminUI\Models\WysiwygMedia;
use Brackets\AdminUI\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Override;

final class WysiwygMediaTest extends TestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->app['db']->connection()->getSchemaBuilder()->create(
            'wysiwyg_media',
            static function (Blueprint $table): void {
                $table->id();
                $table->string('file_path');
                $table->unsignedInteger('wysiwygable_id')->nullable()->index();
                $table->string('wysiwygable_type')->nullable();
                $table->timestamps();
            },
        );
    }

    public function testFillableContainsFilePath(): void
    {
        $model = new WysiwygMedia();

        self::assertContains('file_path', $model->getFillable());
    }

    public function testWysiwygableReturnsMorphToRelation(): void
    {
        $model = new WysiwygMedia();

        self::assertInstanceOf(MorphTo::class, $model->wysiwygable());
    }

    public function testDeletingModelDeletesFileFromDisk(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $this->app->instance(Filesystem::class, $filesystem);

        $model = WysiwygMedia::create(['file_path' => 'uploads/test-image.png']);

        $filesystem->expects(self::once())
            ->method('delete')
            ->with(self::stringContains('uploads/test-image.png'));

        $model->delete();
    }
}
