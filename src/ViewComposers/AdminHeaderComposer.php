<?php

declare(strict_types=1);

namespace Brackets\AdminUI\ViewComposers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\View;

final readonly class AdminHeaderComposer
{
    public function __construct(private AuthManager $authManager, private Config $config)
    {
    }

    public function compose(View $view): void
    {
        $user = $this->resolveUser();

        $view->with('adminUser', $user);
        $view->with('adminUserFullName', $this->resolveFullName($user));
        $view->with('adminUserInitials', $this->resolveInitials($user));
        $view->with('adminUserAvatarUrl', $this->resolveAvatarUrl($user));
    }

    private function resolveUser(): ?Authenticatable
    {
        $guard = $this->config->get('admin-auth.defaults.guard');

        if ($guard !== null) {
            $guardInstance = $this->authManager->guard($guard);
            if ($guardInstance->check()) {
                return $guardInstance->user();
            }
        }

        if ($this->authManager->check()) {
            return $this->authManager->user();
        }

        return null;
    }

    private function resolveFullName(?Authenticatable $user): string
    {
        if ($user === null) {
            return 'Anonymous';
        }

        if (isset($user->full_name) && $user->full_name !== '') {
            return $user->full_name;
        }

        return 'Anonymous';
    }

    private function resolveInitials(?Authenticatable $user): ?string
    {
        if ($user === null) {
            return null;
        }

        if (isset($user->first_name, $user->last_name) && $user->first_name !== '' && $user->last_name !== '') {
            return mb_substr($user->first_name, 0, 1) . mb_substr($user->last_name, 0, 1);
        }

        if (isset($user->name) && $user->name !== '') {
            return mb_substr($user->name, 0, 1);
        }

        return null;
    }

    private function resolveAvatarUrl(?Authenticatable $user): ?string
    {
        if ($user === null) {
            return null;
        }

        return isset($user->avatar_thumb_url) && $user->avatar_thumb_url !== ''
            ? $user->avatar_thumb_url
            : null;
    }
}
