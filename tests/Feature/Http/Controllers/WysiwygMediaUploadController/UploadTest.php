<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature\Http\Controllers\WysiwygMediaUploadController;

use Brackets\AdminUI\Models\WysiwygMedia;
use Brackets\AdminUI\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class UploadTest extends TestCase
{
    private string $uploadDir;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->uploadDir = sys_get_temp_dir() . '/admin-ui-test-uploads-' . uniqid();
        mkdir($this->uploadDir, 0755, true);

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

        // Use a real ImageManager with GD driver — final class can't be mocked
        $imageManager = ImageManager::gd();
        $this->app->instance(ImageManager::class, $imageManager);
        $this->app->instance('image', $imageManager);

        // Configure media folder to use temp directory
        $this->app['config']->set('wysiwyg-media.media_folder', $this->uploadDir . '/uploads');

        // Disable admin middleware for testing
        $this->withoutMiddleware();
    }

    #[Override]
    public function tearDown(): void
    {
        $this->removeDirectory($this->uploadDir);

        parent::tearDown();
    }

    public function testUploadSucceedsWithValidPngImage(): void
    {
        $file = UploadedFile::fake()->image('test.png', 800, 600);

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertOk();
        $response->assertJsonStructure(['file', 'mediaId']);
    }

    public function testUploadSucceedsWithValidJpegImage(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertOk();
        $response->assertJsonStructure(['file', 'mediaId']);
    }

    public function testUploadSucceedsWithValidGifImage(): void
    {
        $file = UploadedFile::fake()->image('test.gif', 800, 600);

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertOk();
        $response->assertJsonStructure(['file', 'mediaId']);
    }

    public function testUploadRejectsInvalidFileType(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Invalid file type.']);
    }

    public function testUploadCreatesWysiwygMediaRecord(): void
    {
        $file = UploadedFile::fake()->image('test.png', 800, 600);

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertOk();
        $mediaId = $response->json('mediaId');
        self::assertNotNull(WysiwygMedia::find($mediaId));
    }

    public function testUploadReturnsFileUrl(): void
    {
        $file = UploadedFile::fake()->image('test.png', 800, 600);

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertOk();
        $fileUrl = $response->json('file');
        self::assertStringContainsString('uploads/', $fileUrl);
        self::assertStringContainsString('test.png', $fileUrl);
    }

    public function testUploadSavesFileToConfiguredMediaFolder(): void
    {
        $customFolder = $this->uploadDir . '/custom-uploads';
        $this->app['config']->set('wysiwyg-media.media_folder', $customFolder);

        $file = UploadedFile::fake()->image('test.png', 800, 600);

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertOk();
        $media = WysiwygMedia::find($response->json('mediaId'));
        self::assertStringStartsWith($customFolder . '/', $media->file_path);
    }

    public function testUploadCreatesDirectoryIfNotExists(): void
    {
        $newFolder = $this->uploadDir . '/new-uploads-dir';
        $this->app['config']->set('wysiwyg-media.media_folder', $newFolder);

        $file = UploadedFile::fake()->image('test.png', 800, 600);

        $response = $this->postJson('/admin/wysiwyg-media', ['fileToUpload' => $file]);

        $response->assertOk();
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($directory);
    }
}
