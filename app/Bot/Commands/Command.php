<?php

namespace App\Bot\Commands;

use App\Helper;
use App\Models\CommandState;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class Command {    
    /**
     * setCommandState
     *
     * @param  Update $update
     * @param  string $command
     * @param  string | null $nextCommand
     * @return void
     */
    public function setCommandState(Update $update, string $command, string | null $nextCommand)
    {
        $from = Helper::getUpdateFrom($update);

        if(!is_null($update->callbackQuery)) {
            $state = $update->callbackQuery;
        } else {
            $state = $update->message;
        }

        CommandState::set([
            'user_id' => $from->id,
            'command' => $command,
            'next_command' => $nextCommand,
            'state' => json_encode($state),
        ]);
    }
    
    /**
     * validate
     *
     * @param  Update $update
     * @param  string $type
     * @param  array $params
     * @return bool
     */
    public function validate(Update $update, string $type, array $params = []): bool
    {
        switch($type) {
            case 'text':
                return !is_null($update->message); 

            case 'inline_keyboard':
                return !is_null($update->callbackQuery) || ( isset($params['prefix']) && preg_match("/^{$params['prefix']}/i", $update->callbackQuery->data) == 1);

            default:
                return true;
        }
    }
    
    /**
     * sendMessage
     *
     * @param  Api $bot
     * @param  Update $update
     * @param  array $params
     * @param  bool $infoCancel
     * @return Message
     */
    public function sendMessage(Api $bot, Update $update, array $params, bool $infoCancel = false): Message
    {
        if(isset($params['text']) && $infoCancel) {
            $params['parse_mode'] = 'Markdown';
            $params['text'] .= "\nTip: use _/cancel_\__command_  to stop this command.";
        }

        $params = array_merge($params, [ 'chat_id' => Helper::getChatId($update) ]);
        return $bot->sendMessage($params);
    }
}