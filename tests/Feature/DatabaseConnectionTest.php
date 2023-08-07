<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_database_connection()
    {
        try {
            DB::connection()->getPdo();
            $this->assertTrue(true);

        } catch(\Exception $e) {
            $this->assertNotTrue(true);
        }
    }
}
