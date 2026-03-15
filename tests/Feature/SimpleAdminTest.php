<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature;

use Brackets\AdminUI\Tests\TestCase;

final class SimpleAdminTest extends TestCase
{
    public function testIfCanDisplayAnAdminListing(): void
    {
        $response = $this->get('/admin/test/index');

        $response->assertOk();

        $content = $response->getContent();

        self::assertStringContainsString('<title>Craftable - Craftable</title>', $content);
        self::assertStringContainsString('Here should be some custom code :)', $content);
        self::assertStringContainsString('</html>', $content);
    }
}
