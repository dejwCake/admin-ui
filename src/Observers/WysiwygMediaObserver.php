<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Observers;

use Brackets\AdminUI\Models\WysiwygMedia;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

final readonly class WysiwygMediaObserver
{
    public function __construct(private Filesystem $filesystem, private Application $application,)
    {
    }

    public function deleted(WysiwygMedia $wysiwygMedia): void
    {
        $this->filesystem->delete($this->application->publicPath($wysiwygMedia->file_path));
    }
}
