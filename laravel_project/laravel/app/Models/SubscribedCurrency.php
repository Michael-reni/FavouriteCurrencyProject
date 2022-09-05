<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscribedCurrency extends Model
{
    use HasFactory;

    protected $table =  'subscribed_currencies';

    protected $fillable = [
        'user_id',
        'subscribed_currency_name',
        'password',
    ];
    
}
