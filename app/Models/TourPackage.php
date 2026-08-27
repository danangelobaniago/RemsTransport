<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * * @var string
     */
    protected $table = 'tour_packages';

    /**
     * The attributes that are mass assignable.
     * * @var array
     */
    protected $fillable = [
        'van_id',
        'driver_id',
        'name',
        'destination',
        'pickup_point',
        'description',
        'inclusions',
        'image',
        'price',
        'max_passengers',
        'duration',
        'tour_date',
        'end_date',
        'is_best_seller'
    ];

    /**
     * The attributes that should be cast to native types.
     * * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'max_passengers' => 'integer',
        'is_best_seller' => 'boolean',
        'tour_date' => 'date',
        'end_date' => 'date',
    ];

    // Add this to your TourPackage.php
    public function van()
    {
        return $this->belongsTo(Van::class, 'van_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
