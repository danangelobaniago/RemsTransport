<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'drivers'; // Matches your phpMyAdmin table

    protected $fillable = [
        'name',
        'contact_number', // Use the column names you have in phpMyAdmin
        'status'
    ];
}
