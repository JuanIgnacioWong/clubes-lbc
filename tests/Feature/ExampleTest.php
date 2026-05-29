<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_redirects_to_inscripciones(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/inscripciones');
    }
}
