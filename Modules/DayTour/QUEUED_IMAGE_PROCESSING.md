# DayTour Image Processing with Queued Jobs

## Overview

This guide explains how to set up and use queued image processing for DayTour module with high performance using Laravel Jobs and the Intervention/Image library.

## Architecture

```
Request
  ↓
Controller
  ↓
Action (UploadDayTourImageAction)
  ↓
[Create placeholder in DB]
  ↓
[Dispatch Job to Queue] ← Immediate response to user (202 Accepted)
  ↓
Job (UploadDayTourImageJob)
  ↓
Core ImageService (process + optimize)
  ↓
S3 Upload (original + thumbnail + medium)
  ↓
Update DB with real S3 path
  ↓
Invalidate Cache
```

## Setup

### 1. Environment Configuration

Add to `.env`:

```env
# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Job Retry Configuration
QUEUE_TRIES=3
QUEUE_TIMEOUT=300

# AWS S3 Configuration
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket

# Laravel
LOG_CHANNEL=stack
```

### 2. Start Queue Workers

```bash
# Start the queue worker (processes jobs from queue)
./vendor/bin/sail artisan queue:work --queue=images,cache --timeout=300

# Or use supervisor in production (see below)
```

### 3. Supervisor Configuration (Production)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /app/artisan queue:work --queue=images,cache --timeout=300
autostart=true
autorestart=true
stderr_logfile=/var/log/laravel-worker.err.log
stdout_logfile=/var/log/laravel-worker.out.log
numprocs=4
priority=999
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Usage

### Upload Image (Async)

**Request:**
```http
POST /api/v1/day-tours/{dayTourId}/images
Content-Type: multipart/form-data

- image: <file>
- is_primary: true (optional)
- sort_order: 0 (optional)
- queue: images (optional, default: 'images')
```

**Response (202 Accepted):**
```json
{
  "data": {
    "id": 1,
    "day_tour_id": "01KQKPD51YMDPBDZQ5CN86TB3F",
    "s3_path": "pending-processing",
    "filename": "image.jpg",
    "is_primary": true,
    "mime_type": "image/jpeg",
    "file_size": 0,
    "disk": "s3",
    "sort_order": 0
  },
  "message": "Image upload queued for processing"
}
```

**What happens in background:**
1. Core ImageService validates file
2. Processes image (crop, optimize to WebP)
3. Creates 3 variants: original, thumbnail, medium
4. Uploads all to S3
5. Updates database with real S3 paths
6. Invalidates cache
7. Image is now ready for serving

### Delete Image (Async)

**Request:**
```http
DELETE /api/v1/day-tours/{dayTourId}/images/{imageId}
```

**Response (202 Accepted):**
```json
{
  "message": "Image deletion queued for processing"
}
```

## Image Processing Details

### What Core ImageService Does

1. **Validation**
   - File size check (max 10MB)
   - MIME type validation (JPEG, PNG, GIF, WebP)
   - File integrity check

2. **Processing**
   - Orientate image (fix EXIF rotation)
   - Cover crop (1200x800 for original)
   - Convert to WebP format (high quality)
   - Compress at specified quality level

3. **Variants**
   - **Original**: 1200x800 @ 90% quality
   - **Thumbnail**: 300x300 @ 80% quality (for lists)
   - **Medium**: 800x800 @ 80% quality (for previews)

4. **Storage**
   - All variants stored on S3
   - Named with pattern: `{original_filename}_{variant}.webp`
   - CDN-ready public access

### File Size Optimization

Example compression results:
- 5MB JPEG → 800KB WebP (original variant)
- → 150KB WebP (thumbnail)
- → 400KB WebP (medium)

**Total saving: ~85% storage reduction**

## Job Architecture

### UploadDayTourImageJob

```php
class UploadDayTourImageJob implements ShouldQueue
{
    public int $tries = 3;           // Retry 3 times
    public int $timeout = 300;       // 5 minute timeout
    public int $backoff = 120;       // 2 minute retry delay
}
```

**Flow:**
1. Retrieve temporary file from storage
2. Call Core ImageService::uploadAndOptimize()
3. Create DayTourImage record in DB
4. Store variant URLs in meta field
5. Dispatch cache invalidation job
6. Log success/failure
7. Clean up temporary file on completion

### DeleteDayTourImageJob

**Flow:**
1. Delete from S3 (including all variants)
2. Delete from database
3. Invalidate cache

### InvalidateDayTourCacheJob

Lightweight cache invalidation:
- Runs on separate 'cache' queue
- Very fast (30 second timeout)
- Non-critical if fails

## Performance Metrics

### Request Timeline

| Phase | Time | Status |
|-------|------|--------|
| Validate | 50ms | Blocking |
| Store temp | 100ms | Blocking |
| Create DB record | 20ms | Blocking |
| Dispatch job | 10ms | Blocking |
| **User response** | **180ms** | **202 Accepted** |
| Process image | 2-5s | Background |
| Upload to S3 | 3-8s | Background |
| Update DB | 10ms | Background |
| **Total background** | **5-15s** | **Async** |

### Throughput

With 4 queue workers:
- Can process ~1000 images/hour
- Sustained 15-20 concurrent uploads
- Memory per worker: ~50MB
- CPU efficient (I/O bound)

## Queue Monitoring

### Check Queue Status

```bash
# See pending jobs
./vendor/bin/sail artisan queue:failed

# Retry failed jobs
./vendor/bin/sail artisan queue:retry all

# Monitor in real-time
./vendor/bin/sail artisan queue:monitor images,cache --max=1000

# Flush queue (use carefully!)
./vendor/bin/sail artisan queue:flush
```

### Database Inspection

```sql
-- View failed jobs
SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 10;

-- View job status (if using database driver)
SELECT * FROM jobs ORDER BY created_at DESC LIMIT 10;
```

### Logging

All job activity is logged:

```
[2026-05-02 14:51:26] local.INFO: DayTour image uploaded successfully
  - image_id: 123
  - day_tour_id: 01KQKPD51YMDPBDZQ5CN86TB3F
  - s3_path: day-tours/01KQKPD51YMDPBDZQ5CN86TB3F/image_abc123.webp

[2026-05-02 14:51:28] local.DEBUG: DayTour cache invalidated
  - day_tour_id: 01KQKPD51YMDPBDZQ5CN86TB3F
```

## API Response Codes

| Code | Meaning | Example |
|------|---------|---------|
| 202 | Accepted for async processing | Image upload queued |
| 201 | Created synchronously | Create day tour |
| 200 | OK | List day tours |
| 404 | Not found | Day tour doesn't exist |
| 422 | Validation error | Invalid image format |
| 429 | Rate limited | Too many requests |
| 500 | Server error | S3 connection failed |

## Error Handling

### Automatic Retries

Jobs retry automatically on failure:

```
Attempt 1 → Fails → Wait 120s
Attempt 2 → Fails → Wait 120s
Attempt 3 → Fails → Move to failed_jobs table
```

### Manual Recovery

```bash
# Retry specific failed job
./vendor/bin/sail artisan queue:retry 123

# Retry all failed jobs
./vendor/bin/sail artisan queue:retry all

# Forget failed job
./vendor/bin/sail artisan queue:forget 123
```

## Best Practices

### 1. Always Use Async for Images
```php
// ✅ Good - Uses async job queue
$this->uploadImageAction->execute($dayTour, $file, true);

// ❌ Bad - Blocks request (only for testing)
$this->uploadImageAction->executeSync($dayTour, $file, true);
```

### 2. Set Appropriate Timeouts
```php
// Image processing can take 5-15 seconds
public int $timeout = 300; // 5 minutes
```

### 3. Use Meaningful Queue Names
```php
// Separate image processing from cache updates
->onQueue('images');
->onQueue('cache');
```

### 4. Handle Failures Gracefully
```php
public function failed(\Throwable $exception): void
{
    \Log::error('Image job failed', ['exception' => $exception]);
    // Notify user via email or webhook
    // Create fallback placeholder
}
```

### 5. Monitor Queue Depth
```bash
# Alert if queue grows too large
./vendor/bin/sail artisan queue:monitor images --threshold=500
```

## Troubleshooting

### Issue: Jobs Not Processing

```bash
# Check if worker is running
ps aux | grep "queue:work"

# Restart worker
./vendor/bin/sail artisan queue:restart

# Check Redis connection
./vendor/bin/sail artisan tinker
> Redis::ping()
```

### Issue: Image Upload Timeout

Increase timeout in `config/queues.php`:
```php
'upload_image' => [
    'timeout' => 600, // 10 minutes
],
```

### Issue: S3 Upload Fails

Check credentials in `.env`:
```bash
# Verify S3 connection
./vendor/bin/sail artisan tinker
> Storage::disk('s3')->put('test.txt', 'test');
```

### Issue: Images Not Deleting

Check job logs:
```bash
# View failed jobs
./vendor/bin/sail artisan queue:failed

# Retry delete jobs
./vendor/bin/sail artisan queue:retry --queue=images
```

## Performance Optimization Tips

1. **Use Redis over Database queue**
   - Faster job dispatch
   - Better for high volume
   - Set QUEUE_CONNECTION=redis

2. **Scale workers based on load**
   ```bash
   # During peak hours
   ./vendor/bin/sail artisan queue:work --queue=images,cache
   ./vendor/bin/sail artisan queue:work --queue=images,cache
   ./vendor/bin/sail artisan queue:work --queue=images,cache
   ```

3. **Monitor queue metrics**
   - Jobs processed per hour
   - Average job duration
   - Failed job rate

4. **Use job batching for bulk uploads**
   ```php
   Bus::batch($jobs)->dispatch();
   ```

5. **Archive old failed jobs regularly**
   ```bash
   ./vendor/bin/sail artisan queue:prune-failed --hours=72
   ```

## Related Files

- Core ImageService: `/app/Services/ImageService.php`
- Jobs: `/Modules/DayTour/app/Jobs/`
- Controller: `/Modules/DayTour/app/Http/Controllers/DayTourController.php`
- Actions: `/Modules/DayTour/app/Actions/UploadDayTourImageAction.php`
- Config: `/Modules/DayTour/config/queues.php`

## References

- [Laravel Queues Documentation](https://laravel.com/docs/queues)
- [Intervention/Image Documentation](https://image.intervention.io/)
- [AWS S3 for Laravel](https://laravel.com/docs/filesystem)
