<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Objects\User;

class SavedRecipe extends Model
{
    use HasFactory;

    protected $table = 'saved_recipe';
    protected $guarded = ['id'];
    public $timestamps = true;

    public static function checkSaved(User $user, string $recipeId): bool
    {
        return self::where('user_id', $user->id)
            ->where('recipe_id', $recipeId)->count() > 0;
    }
}
