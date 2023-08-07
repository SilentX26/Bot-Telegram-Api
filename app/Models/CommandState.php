<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Objects\User;

class CommandState extends Model
{
    use HasFactory;
    
    protected $table = 'command_state';
    protected $guarded = ['id'];
    public $timestamps = true;

    public static function getAll(User $user): Collection 
    {
        return self::where('user_id', $user->getId())
            ->orderBy('id', 'ASC')->get();
    }

    public static function get(User $user, string $command): CommandState
    {
        $data = CommandState::where('user_id', $user->id)
            ->where('command', $command)->first();
        
        $data->state = json_decode($data->state);
        return $data;
    }

    public static function set(array $data): bool
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return self::insert($data);
    }

    public static function empty(User $user): bool
    {
        return self::where('user_id', $user->getId())->delete();
    }
}
