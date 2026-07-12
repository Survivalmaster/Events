<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'created_at',
        'status',
        'handler',
        'type',
        'event_date',
        'event_time',
        'name',
        'district',
        'discord',
        'banner_url',
        'banner_pos_x',
        'banner_pos_y',
        'banner_zoom',
        'description',
        'property_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'banner_pos_x' => 'integer',
            'banner_pos_y' => 'integer',
            'banner_zoom' => 'float',
        ];
    }
}
