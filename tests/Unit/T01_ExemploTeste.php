<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class T01_ExemploTeste extends TestCase
{
    use RefreshDatabase;

    public function test_001_phpunit_executa_assertiva_booleana_basica(): void
    {
        $this->assertTrue(true);
    }
}
