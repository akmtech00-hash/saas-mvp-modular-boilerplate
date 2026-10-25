<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class DocsTest extends TestCase
{
    public function test_interactive_docs_page_loads_successfully(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('api-reference');
        $response->assertSee('/docs/spec');
    }

    public function test_openapi_spec_yaml_is_served(): void
    {
        $response = $this->get('/docs/spec');

        $response->assertStatus(200);
        $response->assertSee('openapi: 3.1.0');
        $response->assertSee('SaaS MVP Modular Monolith API');
    }
}
