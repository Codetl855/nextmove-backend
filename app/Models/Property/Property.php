<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'property_type',
        'listing_type',
        'property_label',
        'size',
        'land_area',
        'property_id',
        'rooms',
        'bedrooms',
        'bathrooms',
        'garages',
        'garage_size',
        'year_built',
        'address',
        'zip_code',
        'city',
        'state',
        'location',
        'price',
        'terms',
        'is_featured',
        'is_active',
        'added_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public const PROPERTY_TYPES = [
        'Apartment',
        'Villa',
        'Studio',
        'House',
        'Office',
    ];

    public function media()
    {
        return $this->hasMany(PropertyMedia::class, 'property_id');
    }
    public function primaryMedia()
    {
        return $this->hasOne(PropertyMedia::class, 'property_id')->where('is_primary', true);
    }
    public function ameneties()
    {
        return $this->hasOne(PropertyAmenity::class, 'property_id');
    }
}
