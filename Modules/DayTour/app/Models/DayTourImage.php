<?php

namespace Modules\DayTour\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DayTourImage extends Model
{
    use SoftDeletes;

    protected $table = 'day_tour_images';

    protected $fillable = [
        'day_tour_id',
        's3_path',
        'is_primary',
        'sort_order',
        'filename',
        'mime_type',
        'file_size',
        'disk',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the day tour that owns this image
     */
    public function dayTour(): BelongsTo
    {
        return $this->belongsTo(DayTour::class, 'day_tour_id', 'id');
    }

    /**
     * Scope for primary images
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope by disk
     */
    public function scopeOnDisk($query, string $disk = 's3')
    {
        return $query->where('disk', $disk);
    }

    /**
     * Get full S3 URL if needed
     */
    public function getFullUrl(): string
    {
        if ($this->disk === 's3') {
            return $this->s3_path;
        }

        return asset('storage/' . $this->s3_path);
    }

    /**
     * Mark as primary and unmark others
     */
    public function markAsPrimary(): bool
    {
        // Unmark all others for this day tour
        DayTourImage::where('day_tour_id', $this->day_tour_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        return $this->update(['is_primary' => true]);
    }
}
