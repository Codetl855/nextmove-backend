<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Model;

class PropertyMedia extends Model
{
    protected $fillable = [
        'property_id',
        'media_type',
        'media_url',
        'is_primary'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
