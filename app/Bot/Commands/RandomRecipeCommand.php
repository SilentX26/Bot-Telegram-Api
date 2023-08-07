<?php

namespace App\Bot\Commands;

use App\Bot\Contracts\CommandContract;
use App\Bot\Objects\InlineKeyboardButton;
use App\Helper;
use App\Library\SpoonApi;
use App\Models\SavedRecipe;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Objects\Update;

class RandomRecipeCommand extends Command implements CommandContract
{
    private static $COMMAND = 'randika_recipe';    
    
    /**
     * handle
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function handle(Api $bot, Update $update)
    {
        $api = new SpoonApi( env('SPOON_API_KEY') );
        $randomRecipe = $api->randomRecipe();

        if(!$randomRecipe) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Ups, something went wrong.',
            ]);
        }

        foreach($randomRecipe['recipes'] as $result) {
            $btnView = new InlineKeyboardButton([[
                'callback_data' => 'viewrecipe-' . $result['id'],
                'text' => 'View Recipe',
            ]]);

            $bot->sendPhoto([
                'chat_id' => Helper::getChatId($update),
                'photo' => InputFile::create($result['image']),
                'caption' => $result['title'],
                'reply_markup' => json_encode([
                    'inline_keyboard' => $btnView->toArray(),
                ])
            ]);
        }
    }
}