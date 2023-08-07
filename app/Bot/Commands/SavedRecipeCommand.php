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

class SavedRecipeCommand extends Command implements CommandContract
{
    private static $COMMAND = 'savedrecipe';
    
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
        $allSavedRecipe = SavedRecipe::where('user_id', $user->id)
            ->orderBy('id', 'DESC')->get();
        
        if($allSavedRecipe->count() == 0) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Ups, you don\'t have any saved recipes.',
            ]);
        }

        foreach($allSavedRecipe as $recipe) {
            $btnView = new InlineKeyboardButton([
                [
                    'callback_data' => 'viewrecipe-' . $recipe->recipe_id,
                    'text' => 'View Recipe',
                ],
                [
                    'callback_data' => 'savedrecipe_delete-' . $recipe->recipe_id,
                    'text' => 'Delete Recipe',
                ],
            ]);

            $bot->sendPhoto([
                'chat_id' => Helper::getChatId($update),
                'photo' => InputFile::create( SpoonApi::getRecipeImageUrl($recipe->recipe_id) ),
                'caption' => $recipe->title,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $btnView->toArray(),
                ])
            ]);
        }
    }
    
    /**
     * store
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function store(Api $bot, Update $update)
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

        $checkSavedRecipe = SavedRecipe::checkSaved($user, $recipeId);
        if(!$checkSavedRecipe) {
            SavedRecipe::create([
                'user_id' => $user->id,
                'recipe_id' => $recipeId,
                'title' => $dataRecipe['title'],
            ]);
        }

        $this->sendMessage($bot, $update, [
            'text' => 'Recipe successfully saved to database!',
        ]);
    }
    
    /**
     * delete
     *
     * @param  Api $bot
     * @param  Update $update
     * @return void
     */
    public function delete(Api $bot, Update $update)
    {
        $validate = $this->validate($update, 'inlinebutton', ['prefix' => self::$COMMAND]);
        if(!$validate) {
            return $this->sendMessage($bot, $update, [
                'text' => 'Please submit the correct command.',
            ]);
        }

        $user = Helper::getUpdateFrom($update);
        $recipeId = Helper::getParamsInlineButton($update->callbackQuery);
        
        SavedRecipe::where('user_id', $user->id)
            ->where('recipe_id', $recipeId)->delete();

        $this->sendMessage($bot, $update, [
            'text' => 'Recipe successfully deleted from database!',
        ]);
    }
}