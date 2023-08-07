<?php

namespace Tests\Feature;

use App\Library\SpoonApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiConnectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_api_connection()
    {
        $api = new SpoonApi( env('SPOON_API_KEY') );
        $test = $api->randomRecipe();

        $this->assertTrue( is_array($test) );
    }
}
