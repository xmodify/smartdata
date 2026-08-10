<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MophAlertDetail extends Model
{
    use HasFactory;

    protected $table = 'moph_alert_detail';

    protected $fillable = [
        'moph_alert_id',
        'user_id',
        'title',
        'message_text',
        'message_html',
        'recipient_count',
        'recipients',
        'status',
        'response_message',
    ];

    protected $casts = [
        'recipients' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mophAlert()
    {
        return $this->belongsTo(MophAlert::class, 'moph_alert_id');
    }
}
