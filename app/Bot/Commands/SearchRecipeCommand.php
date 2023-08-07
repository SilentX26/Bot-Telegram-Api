<?php

namespace App\Bot\Commands;

use App\Bot\Contracts\CommandContract;
use App\Bot\RanStorage;
use App\Helper;
use App\Library\SpoonApi;
use App\Models\CommandState;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use App\Models\Cuisines;
use Illuminate\Support\Facades\DB;
use App\Bot\Objects\InlineKeyboardButton;
use Telegram\Bot\FileUpload\InputFile;

class SearchRecipeCommand extends Command implements CommandContract
{
    private static $COMMAND = '/search_recipe';

    public static $statesHandler = [
        'select_cuisine' => 'selectCuisine',
        'input_keyword' => 'inputKeyword',
    ];
    
    /**
     * handle
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function handle(Api $bot, Update $update)
    {
        $this->setCommandState($update, self::$COMMAND, 'select_cuisine');

        $allCuisines = Cuisines::select([ DB::raw("CONCAT('searchrecipe-', id) AS callback_data"), 'cuisine AS text' ])
            ->orderBy('cuisine', 'ASC')->get();

        $allCuisines->prepend([ 'callback_data' => 'EMPTY', 'text' => 'Did not choose' ]);

        $buttons = new InlineKeyboardButton($allCuisines, 3, '0=1');
        $this->sendMessage($bot, $update, infoCancel: true, params: [
            'text' => "Please select what cuisine you want to look for..",
            'reply_markup' => json_encode([
                'inline_keyboard' => $buttons->toArray(),
            ])
        ]);
    }
    
    /**
     * selectCuisine
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function selectCuisine(Api $bot, Update $update)
    {
        $validate = $this->validate($update, 'inline_keyboard');
        if(!$validate) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Please submit the correct command TOLD.',
            ]);
        }

        $this->setCommandState($update, 'select_cuisine', 'input_keyword');
        if($update->callbackQuery->data !== 'EMPTY') {
            $cuisineId =  str_replace('searchrecipe-', '', $update->callbackQuery->data);
            $dataCuisine = Cuisines::find($cuisineId, ['cuisine']);

            $cuisineMessage = "You choose the type of *{$dataCuisine['cuisine']}* cuisine";
        } else {
            $cuisineMessage = 'You did not choose any type of cuisine, please type the keywords you want';
        }

        $this->sendMessage($bot, $update, [
            'parse_mode' => 'Markdown',
            'text' => $cuisineMessage . ", please type in the keywords you want..",
        ]);
    }
    
    /**
     * inputKeyword
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function inputKeyword(Api $bot, Update $update)
    {
        $validate = $this->validate($update, 'text');
        if(!$validate) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Please submit the correct command.',
            ]);
        }

        $user = $update->message->from;
        $keyword = $update->message->text;
        $dataCommandCuisine = CommandState::get($user, 'select_cuisine');

        $api = new SpoonApi( env('SPOON_API_KEY') );
        $search = $api->searchRecipe([
            'query' => $keyword,
            'cuisine' => $dataCommandCuisine->state->data,
            'number' => 5,
        ]);

        if(!$search) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Ups, something went wrong.',
            ]);

        } else if(count($search['results']) == 0) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Ups, your search result could not be found.',
            ]);
        }

        CommandState::empty($user);
        $this->sendMessage($bot, $update, [
            'parse_mode' => 'Markdown',
            'text' => "Here we display your search results, click *view recipe* to see recipe details.",
        ]);

        foreach($search['results'] as $result) {
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
