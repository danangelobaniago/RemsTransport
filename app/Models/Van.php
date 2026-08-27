<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Van extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'plate_number',
        'seats',
        'price_min',
        'price_max',
        'transmission',
        'image',
        'status'
    ];

    /**
     * Relationship: A van can be assigned to many tour packages.
     */
    public function tourPackages()
    {
        return $this->hasMany(TourPackage::class, 'van_id');
    }
}
