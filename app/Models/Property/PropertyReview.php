<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Model;

class PropertyReview extends Model
{
    protected $fillable = [
        'property_id',
        'reviewer_id',
        'rating',
        'review_text',
    ];
}
