<?php

namespace App\Validation;

class BaseValidation
{
    protected static $doctorDetails = [
        'doctor_details.first_name' => ['required', 'string', 'max:50'],
        'doctor_details.last_name' => ['required', 'string', 'max:50'],
        'doctor_details.homonymous_id' => ['nullable', 'numeric', 'integer'],
    ];

    protected static $messageReviewSharedInputs = [
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

    protected static $user = [
        'first_name' => ['required', 'string', 'max:50'],
        'last_name' => ['required', 'string', 'max:50'],
        'home_address' => ['required', 'string', 'max:100'],
        'email' => [
            'required',
            'string',
            'email',
            'max:50',
            'unique:users',
            'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|it|org|net|edu|gov)$/'
        ],
        'password' => ['required', 'string', 'min:8'],
        'specializations_id.*.id' => ['required', 'exists:specializations,id'],
    ];

    protected static $profile = [
        'office_address' => ['required', 'string', 'min:1', 'max:100'],
        'phone' => ['string', 'regex:/^(?:\+\d{2,3})?[\d\s]{5,12}$/'],
        'services' => ['required', 'string', 'min:4', 'max:400'],
        'curriculum' => ['file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        'photo' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ];

    public static function message()
    {
        return array_merge(
            self::$doctorDetails,
            self::$messageReviewSharedInputs
        );
    }

    public static function review()
    {
        return array_merge(
            self::$doctorDetails,
            self::$messageReviewSharedInputs,
            ['vote' => ['required', 'integer', 'numeric', 'min:0', 'max:5']]
        );
    }

    public static function user()
    {
        return self::$user;
    }

    public static function profile()
    {
        return self::$profile;
    }
}
