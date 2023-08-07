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

class ViewRecipeCommand extends Command implements CommandContract
{
    private static $COMMAND = 'viewrecipe';
    
    /**
     * handle
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function handle(Api $bot, Update $update)
    {
        $validate = $this->validate($update, 'inlinebutton', ['prefix' => self::$COMMAND]);
        if(!$validate) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Please submit the correct command.',
            ]);
        }

        $user = Helper::getUpdateFrom($update);
        $recipeId = Helper::getParamsInlineButton($update->callbackQuery);
        $api = new SpoonApi( env('SPOON_API_KEY') );
        $dataRecipe = $api->detailRecipe($recipeId);

        if(!$dataRecipe) {
            return $this->sendMessage($bot, $update, [ 
                'text' => 'Ups, something went wrong.',
            ]);
        }

        $recipeImage = SpoonApi::getRecipeImageUrl($recipeId);
        $ingredients = array_column($dataRecipe['extendedIngredients'], 'original');
        $ingredientsStr = '';
        foreach($ingredients as $value) {
            $ingredientsStr .= "• {$value}\n";
        }

        $stepsStr = '';
        foreach($dataRecipe['analyzedInstructions'][0]['steps'] as $value) {
            $stepsStr .= "{$value['number']}. {$value['step']}\n";
        }

        $checkSavedRecipe = SavedRecipe::checkSaved($user, $recipeId);
        if(!$checkSavedRecipe) {
            $btnSave = new InlineKeyboardButton([[
                'callback_data' => 'savedrecipe_store-' . $recipeId,
                'text' => 'Save Recipe',
            ]]);

            $replyMarkup = json_encode([
                'inline_keyboard' => $btnSave->toArray(),
            ]);
            
        } else {
            $replyMarkup = null;
        }

        $bot->sendPhoto([
            'chat_id' => Helper::getChatId($update),
            'photo' => InputFile::create($recipeImage),
            'parse_mode' => 'Markdown',
            'caption' => $dataRecipe['title'],
            'reply_markup' => $replyMarkup
        ]);

        $this->sendMessage($bot, $update, [
            'parse_mode' => 'Markdown',
            'text' => trim("Ingredients:\n{$ingredientsStr}"),
        ]);

        $this->sendMessage($bot, $update, [
            'parse_mode' => 'Markdown',
            'text' => trim("How To: \n{$stepsStr}"),
        ]);
    }
}