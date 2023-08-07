<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_setting_telegram_webhook()
    {
        $response = $this->get( url('bot/setup') );
        $response->assertStatus(200);
    }
}
