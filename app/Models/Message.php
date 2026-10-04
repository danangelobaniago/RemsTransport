<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'sender_id',
        'from_admin',
        'is_auto',
        'body',
        'read_at',
    ];

    protected $casts = [
        'from_admin' => 'boolean',
        'is_auto' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toChatArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'from_admin' => $this->from_admin,
            'is_auto' => $this->is_auto,
            'time' => $this->created_at->timezone('Asia/Manila')->format('M j, g:i A'),
        ];
    }
}
