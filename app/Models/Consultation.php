<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'user_id',
        'concern',
        'ai_response',
        'status',
        'conversation_data',
    ];

    protected $casts = [
        'conversation_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ConsultationMessage::class);
    }

    public function sellerMessages()
    {
        return $this->hasMany(ConsultationMessage::class)->where('sender_type', 'admin');
    }
}