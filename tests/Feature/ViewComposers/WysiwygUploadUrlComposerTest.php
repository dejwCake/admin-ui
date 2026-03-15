<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature\ViewComposers;

use Brackets\AdminUI\Tests\TestCase;
use Brackets\AdminUI\ViewComposers\WysiwygUploadUrlComposer;
use Illuminate\Contracts\View\Factory;

final class WysiwygUploadUrlComposerTest extends TestCase
{
    public function testComposesWysiwygUploadUrl(): void
    {
        $composer = $this->app->make(WysiwygUploadUrlComposer::class);

        $viewFactory = $this->app->make(Factory::class);
        $view = $viewFactory->make('brackets/admin-ui::admin.layout.master');

        $composer->compose($view);

        $data = $view->getData();

        self::assertArrayHasKey('wysiwygUploadUrl', $data);
        self::assertStringContainsString('admin/wysiwyg-media', $data['wysiwygUploadUrl']);
    }
}
