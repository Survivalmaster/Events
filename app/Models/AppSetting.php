<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'setting_key';

    protected $keyType = 'string';

    const CREATED_AT = null;

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];
}
