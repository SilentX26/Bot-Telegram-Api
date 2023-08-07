<?php

namespace App\Bot\Commands;

use App\Bot\Contracts\CommandContract;
use App\Helper;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class StartCommand extends Command implements CommandContract
{
    public static $statesHandler = [];
    
    /**
     * handle
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function handle(Api $bot, Update $update)
    {
        $fullName = Helper::getUserFullName($update->message->from);
        $message = "Hello *$fullName*, welcome to Ran Bot. Browse, save, and create recipe of your favorite cuisine here. Available commands:\n";
        $message .= "/start - Start Bot\n/search\_recipe - Search Food Recipe\n/saved\_recipe - List All Saved Recipe\n/random\_recipe - Get Random Food Recipe";
        
        $this->sendMessage($bot, $update, [
            'parse_mode' => 'Markdown',
            'text' => $message,
        ]);
    }
}
