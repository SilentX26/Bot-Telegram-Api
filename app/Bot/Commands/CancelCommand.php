<?php

namespace App\Bot\Commands;

use App\Bot\Contracts\CommandContract;
use App\Helper;
use App\Models\CommandState;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class CancelCommand extends Command implements CommandContract
{    
    /**
     * handle
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function handle(Api $bot, Update $update)
    {
        $user = Helper::getUpdateFrom($update);
        CommandState::empty($user);

        $this->sendMessage($bot, $update, [
            'text' => 'Command successfully cancelled!',
        ]);
    }
}