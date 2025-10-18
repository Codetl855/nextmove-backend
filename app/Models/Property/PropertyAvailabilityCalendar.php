<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Model;

class PropertyAvailabilityCalendar extends Model
{
    protected $fillable = [
        'property_id',
        'date',
        'is_available',
        'price',
    ];
}
