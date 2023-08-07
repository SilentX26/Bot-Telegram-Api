<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\User;
use Tests\TestCase;

class TelegramConnectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_telegram_connection()
    {
        $bot = Telegram::bot();
        $test = $bot->getMe();

        $this->assertTrue( $test instanceof User );
    }
}
