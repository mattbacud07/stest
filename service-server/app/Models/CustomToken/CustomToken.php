<?php

namespace App\Models\CustomToken;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class CustomToken extends SanctumPersonalAccessToken
{
    protected $connection = 'mysql';

    protected $table = 'personal_access_tokens';
}
