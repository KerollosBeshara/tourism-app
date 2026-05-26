<?php
namespace Modules\Media\Traits;

use Modules\Media\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

trait InteractsWithMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Dynamically generates isolated S3 directories based on the model type.
     * Example: "Modules\Destination\Models\DayTour" -> "day-tours/{id}/gallery"
     */
    public function getMediaDirectory(string $collection): string
    {
        $folderBase = Str::plural(Str::kebab(class_basename($this)));
        return "{$folderBase}/{$this->id}/{$collection}";
    }
}