<?php
namespace App;

use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

class Helper {
    public static function getUserFullName(User $user): string
    {
        return $user->firstName . (!empty($user->lastName) ? ' ' : '') . $user->lastName;
    }

    public static function getUpdateMessage(Update $update): Message
    {
        if(!is_null($update->callbackQuery)) {
            return $update->callbackQuery->message;
        }

        return $update->message;
    }

    public static function getUpdateFrom(Update $update): User
    {
        if(!is_null($update->callbackQuery)) {
            return $update->callbackQuery->from;
        }

        return $update->message->from;
    }

    public static function getChatId(Update $update): int
    {
        $message = static::getUpdateMessage($update);
        return $message->chat->id;
    }

    public static function getParamsInlineButton(CallbackQuery $query)
    {
        $exp = explode('-', $query->data);
        return array_splice($exp, 1)[0];
    }
}