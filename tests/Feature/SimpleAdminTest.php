<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Feature;

use Brackets\AdminUI\Tests\TestCase;

class SimpleAdminTest extends TestCase
{
    public function testIfCanDisplayAnAdminListing(): void
    {
        $this->visit('/admin/test/index');

        $this->assertStringContainsString("<title>Craftable - Craftable</title>", $this->response->getContent());

        $this->assertStringContainsString("Here should be some custom code :)", $this->response->getContent());

        $this->assertStringContainsString("</html>", $this->response->getContent());
    }
}
