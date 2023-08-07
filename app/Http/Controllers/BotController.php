<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\CommandState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Update;

class BotController extends Controller
{        
    /**
     * setup
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function setup(Request $request): JsonResponse
    {
        $default = config('telegram.default');
        $botConfig = config('telegram.bots.' . $default);

        $webhookUrl = $botConfig['webhook_url'] . '/' . $botConfig['token'];
        $response = Telegram::setWebhook([ 'url' => $webhookUrl ]);
        return response()->json([ 'status' => $response ]);
    }
    
    /**
     * checkDirectCommand
     *
     * @param  mixed $updates
     * @return mixed
     */
    private function checkDirectCommand(Update $updates): mixed
    {
        if(!$updates->callbackQuery && !$updates->message) return false;
        $messageCommand = $updates->callbackQuery ? $updates->callbackQuery->data : $updates->message->text;

        $allDirectCommands = config('telegram.ran.direct_commands');
        foreach($allDirectCommands as $command => $handler) {
            $command = str_replace('/', "\/", $command);
            if( preg_match("/^{$command}/i", $messageCommand) ) {
                return $handler;
            }
        }

        return false;
    }
    
    /**
     * handleDirectCommand
     *
     * @param  Api $bot
     * @param  Update $updates
     * @param  mixed $handler
     * @return void
     */
    private function handleDirectCommand(Api $bot, Update $updates, $handler)
    {
        $handlerInstance = new $handler[0]();
        $handlerInstance->{$handler[1]}($bot, $updates);
    }
    
    /**
     * handleDefaultCommand
     *
     * @param  Api $bot
     * @param  Update $updates
     * @return void
     */
    private function handleDefaultCommand(Api $bot, Update $updates)
    {
        $defaultCommand = config('telegram.ran.default_command');
        $commandHandler = config('telegram.ran.commands')[ $defaultCommand ] ?? null;
        
        if($commandHandler) {
            $handlerInstance = new $commandHandler();
            $handlerInstance->handle($bot, $updates);
        }
    }
    
    /**
     * handleWebhook
     *
     * @param  Request $request
     * handle webhook updates from telegram
     * 
     * @return void
     */
    public function handleWebhook(Request $request)
    {
        $updates = Telegram::getWebhookUpdate();
        $message = Helper::getUpdateMessage($updates);
        $user = Helper::getUpdateFrom($updates);

        $bot = Telegram::bot();
        $allCommands = config('telegram.ran.commands');
        $activeCommandStates = CommandState::getAll($user);
        
        $runningCommand = $activeCommandStates->count() > 0;
        $command = $runningCommand ? $activeCommandStates->first()->command : trim($message->getText());

        $checkDirect = $this->checkDirectCommand($updates);
        if($checkDirect !== false) {
            // handle direct command
            $this->handleDirectCommand($bot, $updates, $checkDirect);
            return response()->json(['status' => true], 200);
        }

        $commandHandler = $allCommands[ $command ] ?? null;
        if(is_null($commandHandler)) {
            // handle default command
            $this->handleDefaultCommand($bot, $updates);
            return response()->json(['status' => true], 200);
        }
        
        $commandHandlerInstance = new $commandHandler($bot, $updates);
        if($runningCommand) {
            $lastCommand = $activeCommandStates->last();
            $stateHandler = $commandHandler::$statesHandler[$lastCommand->next_command] ?? null;
            
            if(is_null($stateHandler)) {
                return response()->json(['status' => false], 400);
            }

            $commandHandlerInstance->{$stateHandler}($bot, $updates);
            return response()->json(['status' => true], 200);
        } else {
            $commandHandlerInstance->handle($bot, $updates);
            return response()->json(['status' => true], 200);
        }
    }
}
 