<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class Member extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'members';

    protected $fillable = [
        'uuid', 'email', 'fullname', 'username', 'password', 'phone_number', 'registered_from', 'user_code', 'referral', 
        'last_login', 'level', 'balance', 'is_loggedin'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function boot()
    {
        parent::boot(); //

        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }
}
