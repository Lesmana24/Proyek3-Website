<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pengguna;

class PlantHealthScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'ai_health_status',
        'disease_name',
        'confidence_score',
        'plant_name',
        'care_light',
        'care_water',
        'care_temperature',
        'problems_list',
    ];

    /**
     * Konversi string JSON di database otomatis menjadi tipe Data Array PHP.
     */
    protected $casts = [
        'problems_list' => 'array',
    ];


    /**
     * Relationship with the User model.
     * Allows linking a scan to a specific user.
     */
    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    /**
     * Relationship with ChatHistory model.
     */
    public function chatHistories()
    {
        return $this->hasMany(ChatHistory::class, 'plant_health_scan_id');
    }
}
