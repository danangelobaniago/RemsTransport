<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinerTrip extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * * If your database table is NOT named 'joiner_trips',
     * change the name below to match your actual table.
     */
    protected $table = 'joiner_trips';

    /**
     * The attributes that are mass assignable.
     * * Add the column names you have in your database here
     * so Laravel can save data to them.
     */
    protected $fillable = [
    'destination',
    'meetup_point',
    'trip_date',
    'total_seats',
    'available_seats',
    'price_per_seat',
    'van',
    'status',
];
}
