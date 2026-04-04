<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_health_scan_id',
        'sender',
        'message',
    ];

    public function plantHealthScan()
    {
        return $this->belongsTo(PlantHealthScan::class, 'plant_health_scan_id');
    }
}
