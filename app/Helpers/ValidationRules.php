<?php

namespace App\Helpers;

class ValidationRules
{
    public static $user = [
        'doctor_details.first_name' => ['required', 'string', 'max:50'],
        'doctor_details.last_name' => ['required', 'string', 'max:50'],
        'doctor_details.homonymous_id' => ['nullable', 'numeric', 'integer'],
    ];

    public static $messageReviewSharedInputs = [
        'first_name' => ['required', 'string', 'max:50'],
        'last_name' => ['required', 'string', 'max:50'],
        'email' => [
            'required',
            'string',
            'email',
            'max:50',
            'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|it|org|net|edu|gov)$/'
        ],
        'content' => ['required', 'string', 'min:5', 'max:300'],
    ];

    public static function message()
    {
        return array_merge(
            self::$user,
            self::$messageReviewSharedInputs
        );
    }

    public static function review()
    {
        return array_merge(
            self::$user,
            self::$messageReviewSharedInputs,
            ['votes' => ['required', 'integer', 'numeric', 'min:0', 'max:5']]
        );
    }
}
