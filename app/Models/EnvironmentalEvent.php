<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvironmentalEvent extends Model
{
    protected $fillable = [
        'created_at',
        'event_id',
        'faction_flags',
        'weight',
        'type',
        'name',
        'district',
        'banner_url',
        'banner_pos_x',
        'banner_pos_y',
        'banner_zoom',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'banner_pos_x' => 'integer',
            'banner_pos_y' => 'integer',
            'banner_zoom' => 'float',
        ];
    }
}
