<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Frontend compilation is verified separately by `npm run build` in CI.
        // Feature tests should focus on Laravel behavior without requiring a Vite manifest.
        $this->withoutVite();
    }
}
