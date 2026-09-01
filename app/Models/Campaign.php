<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'target_amount',
        'collected_amount',
        'deadline',
        'video_url',
        'status',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function backings()
    {
        return $this->hasMany(Backing::class);
    }

    public function images()
    {
        return $this->hasMany(CampaignImage::class);
    }

    public function tiers()
    {
        return $this->hasMany(CampaignTier::class);
    }

    public function updates()
    {
        return $this->hasMany(CampaignUpdate::class);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return round(($this->current_amount / $this->target_amount) * 100, 2);
    }

    public function getRemainingDaysAttribute()
    {
        return now()->diffInDays($this->deadline, false);
    }

    protected $appends = ['progress_percentage', 'remaining_days'];
}
