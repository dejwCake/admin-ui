<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature;

use Brackets\AdminUI\Tests\TestCase;

class SimpleAdminTest extends TestCase
{
    public function testIfCanDisplayAnAdminListing(): void
    {
        $this->visit('/admin/test/index');

        self::assertStringContainsString("<title>Craftable - Craftable</title>", $this->response->getContent());

        self::assertStringContainsString("Here should be some custom code :)", $this->response->getContent());

        self::assertStringContainsString("</html>", $this->response->getContent());
    }
}
