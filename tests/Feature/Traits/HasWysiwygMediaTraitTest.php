<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature\Traits;

use Brackets\AdminUI\Models\WysiwygMedia;
use Brackets\AdminUI\Tests\TestCase;
use Brackets\AdminUI\Tests\TestWysiwygableModel;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;
use Override;

final class HasWysiwygMediaTraitTest extends TestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();

        $schemaBuilder->create('wysiwyg_media', static function (Blueprint $table): void {
            $table->id();
            $table->string('file_path');
            $table->unsignedInteger('wysiwygable_id')->nullable()->index();
            $table->string('wysiwygable_type')->nullable();
            $table->timestamps();
        });

        $schemaBuilder->create('test_wysiwygable_models', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function testWysiwygMediaReturnsMorphManyRelation(): void
    {
        $model = new TestWysiwygableModel();

        self::assertInstanceOf(MorphMany::class, $model->wysiwygMedia());
    }

    public function testSavedEventAttachesWysiwygMediaIds(): void
    {
        $media1 = WysiwygMedia::create(['file_path' => 'uploads/image1.png']);
        $media2 = WysiwygMedia::create(['file_path' => 'uploads/image2.png']);

        $this->app['request']->merge(['wysiwygMedia' => [$media1->id, $media2->id]]);

        $model = TestWysiwygableModel::create(['name' => 'Test']);

        self::assertCount(2, $model->wysiwygMedia);
        self::assertSame($model->id, (int) $media1->fresh()->wysiwygable_id);
    }

    public function testSavedEventIgnoresNonIntegerIds(): void
    {
        $media = WysiwygMedia::create(['file_path' => 'uploads/image.png']);

        $this->app['request']->merge(['wysiwygMedia' => ['not-an-int', $media->id]]);

        $model = TestWysiwygableModel::create(['name' => 'Test']);

        // Only integer IDs are attached; 'not-an-int' is filtered, $media->id is int so attached
        self::assertCount(1, $model->wysiwygMedia);
    }

    public function testDeletedEventDeletesRelatedWysiwygMedia(): void
    {
        $model = TestWysiwygableModel::create(['name' => 'Test']);

        $media = WysiwygMedia::create(['file_path' => 'uploads/image.png']);
        $model->wysiwygMedia()->save($media);

        // Stub filesystem to prevent actual file deletion
        $filesystem = $this->createStub(\Illuminate\Filesystem\Filesystem::class);
        $filesystem->method('delete')->willReturn(true);
        $this->app->instance(\Illuminate\Filesystem\Filesystem::class, $filesystem);

        $model->delete();

        self::assertNull(WysiwygMedia::find($media->id));
    }
}
