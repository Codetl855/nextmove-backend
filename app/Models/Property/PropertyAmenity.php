<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Model;

class PropertyAmenity extends Model
{
    protected $fillable = [
        'property_id',
        'amenity_names',
    ];
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
