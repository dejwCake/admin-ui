<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature\ViewComposers;

use Brackets\AdminUI\Tests\TestCase;
use Brackets\AdminUI\ViewComposers\AdminHeaderComposer;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\Factory;
use Mockery;

final class AdminHeaderComposerTest extends TestCase
{
    public function testComposesAnonymousWhenNoUserAuthenticated(): void
    {
        $data = $this->composeWith(null);

        self::assertNull($data['adminUser']);
        self::assertSame('Anonymous', $data['adminUserFullName']);
        self::assertNull($data['adminUserInitials']);
        self::assertNull($data['adminUserAvatarUrl']);
    }

    public function testComposesFullNameFromUserProperty(): void
    {
        $user = $this->createUser(['full_name' => 'John Doe']);

        $data = $this->composeWith($user);

        self::assertSame('John Doe', $data['adminUserFullName']);
    }

    public function testFallsBackToAnonymousWhenFullNameIsEmpty(): void
    {
        $user = $this->createUser(['full_name' => '']);

        $data = $this->composeWith($user);

        self::assertSame('Anonymous', $data['adminUserFullName']);
    }

    public function testComposesInitialsFromFirstAndLastName(): void
    {
        $user = $this->createUser(['first_name' => 'John', 'last_name' => 'Doe']);

        $data = $this->composeWith($user);

        self::assertSame('JD', $data['adminUserInitials']);
    }

    public function testComposesInitialsFromNameWhenNoFirstLast(): void
    {
        $user = $this->createUser(['name' => 'John']);

        $data = $this->composeWith($user);

        self::assertSame('J', $data['adminUserInitials']);
    }

    public function testInitialsAreNullWhenNoNameProperties(): void
    {
        $user = $this->createUser([]);

        $data = $this->composeWith($user);

        self::assertNull($data['adminUserInitials']);
    }

    public function testComposesAvatarUrlFromUserProperty(): void
    {
        $user = $this->createUser(['avatar_thumb_url' => 'https://example.com/avatar.jpg']);

        $data = $this->composeWith($user);

        self::assertSame('https://example.com/avatar.jpg', $data['adminUserAvatarUrl']);
    }

    public function testAvatarUrlIsNullWhenPropertyMissing(): void
    {
        $user = $this->createUser([]);

        $data = $this->composeWith($user);

        self::assertNull($data['adminUserAvatarUrl']);
    }

    public function testAvatarUrlIsNullWhenPropertyIsEmpty(): void
    {
        $user = $this->createUser(['avatar_thumb_url' => '']);

        $data = $this->composeWith($user);

        self::assertNull($data['adminUserAvatarUrl']);
    }

    public function testResolvesUserFromConfiguredAdminGuard(): void
    {
        $user = $this->createUser(['full_name' => 'Admin User']);

        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('check')->andReturn(true);
        $guard->shouldReceive('user')->andReturn($user);

        $authManager = Mockery::mock(AuthManager::class);
        $authManager->shouldReceive('guard')->with('admin')->andReturn($guard);

        $config = $this->app->make(Config::class);
        $config->set('admin-auth.defaults.guard', 'admin');

        $composer = new AdminHeaderComposer($authManager, $config);
        $data = $this->composeView($composer);

        self::assertSame('Admin User', $data['adminUserFullName']);
    }

    public function testFallsBackToDefaultGuardWhenAdminGuardNotConfigured(): void
    {
        $user = $this->createUser(['full_name' => 'Default User']);

        $authManager = Mockery::mock(AuthManager::class);
        $authManager->shouldReceive('check')->andReturn(true);
        $authManager->shouldReceive('user')->andReturn($user);

        $config = $this->app->make(Config::class);
        $config->set('admin-auth.defaults.guard', null);

        $composer = new AdminHeaderComposer($authManager, $config);
        $data = $this->composeView($composer);

        self::assertSame('Default User', $data['adminUserFullName']);
    }

    /**
     * @param array<string, string> $attributes
     */
    private function createUser(array $attributes): Authenticatable
    {
        $user = Mockery::mock(Authenticatable::class);

        foreach ($attributes as $key => $value) {
            $user->shouldReceive('__isset')->with($key)->andReturn(true);
            $user->shouldReceive('__get')->with($key)->andReturn($value);
            $user->{$key} = $value;
        }

        // For properties not in attributes, __isset returns false
        $user->shouldReceive('__isset')->andReturn(false)->byDefault();

        return $user;
    }

    /**
     * @return array<string, Authenticatable|string|null>
     */
    private function composeWith(?Authenticatable $user): array
    {
        $authManager = Mockery::mock(AuthManager::class);
        $config = $this->app->make(Config::class);
        $config->set('admin-auth.defaults.guard', null);

        if ($user !== null) {
            $authManager->shouldReceive('check')->andReturn(true);
            $authManager->shouldReceive('user')->andReturn($user);
        } else {
            $authManager->shouldReceive('check')->andReturn(false);
        }

        $composer = new AdminHeaderComposer($authManager, $config);

        return $this->composeView($composer);
    }

    /**
     * @return array<string, Authenticatable|string|null>
     */
    private function composeView(AdminHeaderComposer $composer): array
    {
        $viewFactory = $this->app->make(Factory::class);
        $view = $viewFactory->make('brackets/admin-ui::admin.partials.header');

        $composer->compose($view);

        return $view->getData();
    }
}
