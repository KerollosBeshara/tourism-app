# Testing DayTour Image Processing

## Quick Start (Development)

### 1. Setup Local Environment

```bash
# Copy environment file
cp Modules/DayTour/.env.example .env.local

# For development, you can use sync queue (processes immediately)
# Edit .env
QUEUE_CONNECTION=sync  # Use sync for testing (processes jobs immediately)

# Or use redis for realistic testing
QUEUE_CONNECTION=redis
```

### 2. Test with Artisan Tinker

```bash
./vendor/bin/sail artisan tinker
```

```php
// Create a test day tour
$dayTour = \Modules\DayTour\Models\DayTour::first();

// Simulate file upload
$file = new \Illuminate\Http\UploadedFile(
    path: base_path('tests/fixtures/sample.jpg'),
    originalName: 'sample.jpg',
    mimeType: 'image/jpeg'
);

// Execute upload (with sync queue, happens immediately)
$action = app(\Modules\DayTour\Actions\UploadDayTourImageAction::class);
$image = $action->execute($dayTour, $file, true);

echo $image->id;
echo $image->s3_path;  // Should have real S3 path now
```

### 3. Test with cURL

```bash
# Get auth token first
TOKEN=$(./vendor/bin/sail artisan tinker <<'PHP'
$user = \App\Models\User::first();
echo $user->createToken('test')->plainTextToken;
PHP
)

# Create day tour
curl -X POST http://localhost/api/v1/day-tours \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "agency_id": "uuid-here",
    "city_id": 1,
    "destination_id": 1,
    "title": {"en": "Test Tour"},
    "description": {"en": "Test Description"}
  }'

# Upload image (async)
curl -X POST http://localhost/api/v1/day-tours/{id}/images \
  -H "Authorization: Bearer $TOKEN" \
  -F "image=@/path/to/image.jpg" \
  -F "is_primary=true"
```

## Unit Testing

### Setup Test Database

```bash
./vendor/bin/sail artisan migrate:fresh --database=testing
./vendor/bin/sail artisan db:seed --database=testing
```

### Example Test: Image Upload Job

```php
<?php

namespace Modules\DayTour\Tests\Unit\Jobs;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Modules\DayTour\Jobs\UploadDayTourImageJob;
use Modules\DayTour\Models\DayTour;
use Modules\DayTour\Models\DayTourImage;
use Tests\TestCase;

class UploadDayTourImageJobTest extends TestCase
{
    public function test_job_processes_image_successfully()
    {
        // Create day tour
        $dayTour = DayTour::factory()->create();
        
        // Create fake image
        $file = UploadedFile::fake()->image('test.jpg', 1200, 800);
        Storage::fake('local');
        $file->storeAs('temp', 'test.jpg', 'local');
        
        // Create image record with temp path
        $image = DayTourImage::create([
            'day_tour_id' => $dayTour->id,
            's3_path' => 'temp/test.jpg',
        ]);
        
        // Mock S3 storage
        Storage::fake('s3');
        
        // Dispatch job
        $job = new UploadDayTourImageJob($image);
        $job->handle(app(\App\Services\ImageService::class));
        
        // Assert image was processed
        $this->assertNotNull($image->fresh()->s3_path);
        $this->assertStringNotContainsString('temp', $image->fresh()->s3_path);
        
        // Assert variants exist
        $meta = $image->fresh()->meta;
        $this->assertArrayHasKey('thumbnail_url', $meta);
        $this->assertArrayHasKey('medium_url', $meta);
    }
}
```

### Example Test: Delete Image Job

```php
public function test_delete_job_removes_from_s3()
{
    $image = DayTourImage::factory()->create();
    Storage::fake('s3');
    
    // Dispatch delete job
    $job = new DeleteDayTourImageJob($image);
    $job->handle(app(\Modules\DayTour\Services\S3ImageService::class));
    
    // Assert deleted from DB
    $this->assertNull(DayTourImage::find($image->id));
}
```

### Run Tests

```bash
# Run all tests
./vendor/bin/sail artisan test

# Run specific test
./vendor/bin/sail artisan test --filter=UploadDayTourImageJobTest

# With coverage
./vendor/bin/sail artisan test --coverage
```

## Integration Testing

### Test Image Upload Flow (End-to-End)

```bash
# 1. Start queue worker in background
./vendor/bin/sail artisan queue:work &

# 2. Upload image
curl -X POST http://localhost/api/v1/day-tours/{id}/images \
  -H "Authorization: Bearer $TOKEN" \
  -F "image=@/path/to/image.jpg"

# 3. Check job was processed
./vendor/bin/sail artisan queue:failed

# 4. Verify image in database
./vendor/bin/sail artisan tinker
> \Modules\DayTour\Models\DayTourImage::latest()->first();
```

### Monitor Queue During Testing

```bash
# Terminal 1: Start worker
./vendor/bin/sail artisan queue:work --queue=images,cache

# Terminal 2: Monitor queue
./vendor/bin/sail artisan queue:monitor images,cache --max=100

# Terminal 3: Run test requests
./vendor/bin/sail artisan tinker
> # Run test code here
```

## Performance Testing

### Load Test Image Uploads

```bash
# Using Apache Bench (ab)
ab -n 100 -c 10 \
  -H "Authorization: Bearer $TOKEN" \
  -p image_upload.json \
  http://localhost/api/v1/day-tours/{id}/images

# Using wrk (better)
wrk -t4 -c100 -d30s \
  -H "Authorization: Bearer $TOKEN" \
  -s upload_script.lua \
  http://localhost/api/v1/day-tours/{id}/images
```

### Monitor Performance Metrics

```bash
# Check queue depth
./vendor/bin/sail artisan queue:monitor images --max=1000

# Check memory usage
./vendor/bin/sail artisan tinker
> $jobs = \Illuminate\Queue\Failed\FailedJobProvider::all();
> count($jobs);

# Check processing time
./vendor/bin/sail artisan queue:work --queue=images --verbose
```

## Common Issues and Solutions

### Issue 1: Jobs Not Processing

```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Start worker if not running
./vendor/bin/sail artisan queue:work --queue=images,cache

# Check for errors
tail -f storage/logs/laravel.log
```

### Issue 2: S3 Upload Fails

```bash
# Verify S3 credentials
./vendor/bin/sail artisan tinker
> Storage::disk('s3')->put('test.txt', 'test content');

# Check disk configuration
> config('filesystems.disks.s3');
```

### Issue 3: Image Not Found After Upload

```bash
# Check if job was queued but failed
./vendor/bin/sail artisan queue:failed

# Retry failed job
./vendor/bin/sail artisan queue:retry {id}

# Check database for placeholder
> \Modules\DayTour\Models\DayTourImage::where('s3_path', 'pending-processing')->get();
```

## Debugging Tips

### Enable Verbose Job Output

```bash
./vendor/bin/sail artisan queue:work --verbose
```

### Log Job Details

Add to your job's `handle()` method:

```php
public function handle(ImageService $imageService): void
{
    \Log::debug('Starting image processing', [
        'image_id' => $this->image->id,
        'current_path' => $this->image->s3_path,
    ]);
    
    // ... processing ...
    
    \Log::info('Image processing completed', [
        'image_id' => $this->image->id,
        'new_path' => $this->image->s3_path,
    ]);
}
```

### Monitor Redis Queue

```bash
./vendor/bin/sail redis-cli

# In Redis CLI
KEYS "queues:*"  # See all queues
LLEN queues:images  # Length of images queue
LPOP queues:images  # Get first job
```

## Checklists

### Before Going to Production

- [ ] Queue driver set to Redis (not sync)
- [ ] Queue worker running via supervisor
- [ ] S3 credentials configured
- [ ] Disk space monitored on S3
- [ ] Failed job notifications setup
- [ ] Queue depth monitoring alert
- [ ] Job timeout values validated
- [ ] Retry logic tested with real failures
- [ ] Error logging configured
- [ ] CloudFront CDN setup (optional)

### Daily Checks

```bash
# Check failed jobs
./vendor/bin/sail artisan queue:failed

# Monitor queue depth
./vendor/bin/sail artisan queue:monitor images

# Prune old failed jobs
./vendor/bin/sail artisan queue:prune-failed --hours=72

# Check Redis memory
./vendor/bin/sail redis-cli info memory
```

## Performance Baseline

Expected metrics with proper setup:

| Metric | Target |
|--------|--------|
| Image upload time (sync) | <180ms |
| Job processing time | 5-15s |
| Throughput per worker | 250 images/hour |
| Memory per worker | 50-100MB |
| Queue latency | <100ms |
| S3 upload time | 3-8s |
| Failed job rate | <1% |

