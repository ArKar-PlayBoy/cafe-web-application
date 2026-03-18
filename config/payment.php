<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KBZ Pay Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your KBZ Pay phone number for receiving payments.
    | Customers will see this number when they select KBZ Pay as payment method.
    |
    */

    'kbz_pay_phone' => env('KBZ_PAY_PHONE', '09781234567'),

    /*
    |--------------------------------------------------------------------------
    | Business Phone Number
    |--------------------------------------------------------------------------
    |
    | General business contact phone number.
    |
    */

    'business_phone' => env('BUSINESS_PHONE', '09781234567'),
];
