<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'name';

    protected $keyType = 'string';

    protected $fillable = ['name','currency_code','exchange_rate'];
    
    protected $table =  'currencies';

    public $incrementing = false;

    protected $hidden = [
        'created_at',
    ];

    //public $timestamps = false;
}
