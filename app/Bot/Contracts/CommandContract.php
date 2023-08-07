<?php
namespace App\Bot\Contracts;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

interface CommandContract {    
    /**
     * handle
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function handle(Api $bot, Update $update);
}