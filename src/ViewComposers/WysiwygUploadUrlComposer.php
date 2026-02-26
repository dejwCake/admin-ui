<?php

declare(strict_types=1);

namespace Brackets\AdminUI\ViewComposers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\View\View;

final readonly class WysiwygUploadUrlComposer
{
    public function __construct(private UrlGenerator $urlGenerator)
    {
    }

    public function compose(View $view): void
    {
        $view->with('wysiwygUploadUrl', $this->urlGenerator->route('brackets/admin-ui::wysiwyg-upload'));
    }
}
