# DayTour Image Processing - Quick Reference

## 🚀 Start Here

### Local Development (5 minutes)

```bash
# 1. Update .env
QUEUE_CONNECTION=sync  # Processes jobs immediately for testing

# 2. Run migrations
./vendor/bin/sail artisan migrate

# 3. Test in Tinker
./vendor/bin/sail artisan tinker

# Create test data
$dayTour = \Modules\DayTour\Models\DayTour::factory()->create();
$file = \Illuminate\Http\UploadedFile::fake()->image('test.jpg');

# Upload image
$action = app(\Modules\DayTour\Actions\UploadDayTourImageAction::class);
$image = $action->execute($dayTour, $file, true);

# Check result
$image->fresh();
```

### Production Deployment

```bash
# 1. Configure .env
QUEUE_CONNECTION=redis
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=your_bucket

# 2. Start queue worker
./vendor/bin/sail artisan queue:work --queue=images,cache

# 3. Monitor
./vendor/bin/sail artisan queue:monitor images
```

## 📋 Common Tasks

### Upload Single Image

```php
$action = app(\Modules\DayTour\Actions\UploadDayTourImageAction::class);

$image = $action->execute(
    dayTour: $dayTour,
    file: $request->file('image'),
    isPrimary: true,
    sortOrder: 0,
    queue: 'images'
);

// Returns placeholder immediately
// Job processes in background
```

### Upload Multiple Images

```php
$files = $request->file('images');

$images = $action->uploadBatch(
    dayTour: $dayTour,
    files: $files,
    sortOrder: 0
);

// All jobs dispatched, immediate response
```

### Delete Image

```php
// Via controller (automatic)
DELETE /api/v1/day-tours/{id}/images/{imageId}

// Via action
\Modules\DayTour\Jobs\DeleteDayTourImageJob::dispatch($image)
    ->onQueue('images');
```

### Get Image URLs

```php
$image = DayTourImage::find($imageId);

// All URLs available after job completes
$image->s3_path;              // Original (1200x800)
$image->meta['thumbnail_url']; // Thumbnail (300x300)
$image->meta['medium_url'];    // Medium (800x800)
```

### Batch Operations

```php
// Upload batch
$images = $action->uploadBatch($dayTour, $files);

// Delete batch
$dayTour->images()->each(fn($img) => 
    \Modules\DayTour\Jobs\DeleteDayTourImageJob::dispatch($img)
);

// Update sort order batch
$dayTour->images()
    ->update(['sort_order' => DB::raw('ROW_NUMBER() OVER (ORDER BY created_at)')])
    ->each(fn($img) => 
        \Modules\DayTour\Jobs\InvalidateDayTourCacheJob::dispatch($dayTour)
    );
```

## 🔍 Debugging

### Check Queue Status

```bash
# See pending jobs
./vendor/bin/sail artisan queue:failed

# Monitor queue depth
./vendor/bin/sail artisan queue:monitor images --max=100

# Retry failed jobs
./vendor/bin/sail artisan queue:retry {job_id}
```

### Check Logs

```bash
# Real-time logs
tail -f storage/logs/laravel.log | grep Image

# Search for errors
grep -i "error\|failed" storage/logs/laravel.log | tail -20
```

### Verify S3 Upload

```bash
# In Tinker
$image = DayTourImage::find($imageId);
\Illuminate\Support\Facades\Storage::disk('s3')->exists($image->s3_path);

# Get URL
\Illuminate\Support\Facades\Storage::disk('s3')->url($image->s3_path);
```

### Check Redis Queue

```bash
# In Redis CLI
./vendor/bin/sail redis-cli

KEYS "queues:*"           # All queues
LLEN queues:images        # Number of pending jobs
LPOP queues:images        # Get first job (don't do this in production!)
```

## 📊 Performance Metrics

### Response Times
| Operation | Time |
|-----------|------|
| Validate file | 50ms |
| Store temp | 100ms |
| Create DB record | 20ms |
| Dispatch job | 10ms |
| **User response** | **~180ms** |

### Job Processing
| Task | Time |
|------|------|
| Process image | 2-5s |
| Upload to S3 | 3-8s |
| Update DB | 10ms |
| Cache invalidation | 5ms |
| **Total** | **5-15s** |

### Throughput (Per Worker)
- ~250 images/hour
- 15-20 concurrent uploads
- 50-100MB memory
- <1% CPU (I/O bound)

## 🛠️ Configuration Files

### Environment (.env)
```env
QUEUE_CONNECTION=redis
REDIS_HOST=redis
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_BUCKET=xxx
```

### Queue Config (config/queues.php)
```php
'images' => [
    'tries' => 3,           // Retry 3 times
    'timeout' => 300,       // 5 minute timeout
    'backoff' => 120,       // 2 minute delay
]
```

### Service Provider (automatically loaded)
Binds:
- `UploadDayTourImageAction`
- `DayTourRepository`
- `DayTourCacheService`
- `S3ImageService`

## 📚 Documentation

- **ASYNC_IMPLEMENTATION_SUMMARY.md** - Overview & architecture
- **QUEUED_IMAGE_PROCESSING.md** - Setup & usage guide (9.8KB)
- **TESTING_GUIDE.md** - Testing strategies (8.2KB)
- **IMPLEMENTATION_GUIDE.md** - Complete reference (8KB)

## 💡 Best Practices

✅ **Always use async** - Never process images in request  
✅ **Check queue depth** - Monitor with `queue:monitor`  
✅ **Retry failed jobs** - Use `queue:retry` command  
✅ **Log everything** - Enable debug logging for troubleshooting  
✅ **Monitor S3 costs** - Track upload volume  
✅ **Use appropriate timeouts** - Image processing needs 5+ minutes  
✅ **Separate queues** - Images on 'images', cache on 'cache'  

## ⚠️ Common Mistakes

❌ **Sync queue in production** - Use Redis instead  
❌ **Low timeout value** - Image processing takes time  
❌ **No queue monitoring** - Check `queue:failed` regularly  
❌ **Not validating files** - Use form requests  
❌ **Direct S3 calls** - Always use ImageService  
❌ **Storing files locally** - Use S3 for scaling  
❌ **Ignoring failed jobs** - They need retry or deletion  

## 🚨 Troubleshooting

### Problem: Jobs Not Running
```bash
# Check worker is running
ps aux | grep queue:work

# Start worker
./vendor/bin/sail artisan queue:work --verbose
```

### Problem: S3 Upload Fails
```bash
# Check credentials
AWS_ACCESS_KEY_ID in .env?
AWS_SECRET_ACCESS_KEY in .env?
AWS_BUCKET exists?

# Test connection
./vendor/bin/sail artisan tinker
> \Illuminate\Support\Facades\Storage::disk('s3')->put('test', 'test');
```

### Problem: Image Stuck "Pending"
```bash
# Check failed jobs
./vendor/bin/sail artisan queue:failed

# Check logs
tail -f storage/logs/laravel.log | grep pending

# Retry
./vendor/bin/sail artisan queue:retry {job_id}
```

### Problem: High Memory Usage
```bash
# Reduce workers
# Each uses 50-100MB, so 4 workers = 200-400MB

# Reduce batch size
# uploadBatch() - process in smaller batches

# Check for leaks
# Review job handle() methods
```

## 🎯 Next Steps

1. **Setup .env** with Redis and AWS credentials
2. **Start queue worker** - `queue:work --queue=images,cache`
3. **Test upload** - Follow quick start above
4. **Monitor logs** - `tail -f storage/logs/laravel.log`
5. **Load test** - Use provided TESTING_GUIDE.md

## 📞 Support

Check documentation files first:
- `ASYNC_IMPLEMENTATION_SUMMARY.md` - Overview
- `QUEUED_IMAGE_PROCESSING.md` - Setup help
- `TESTING_GUIDE.md` - Test examples
- `IMPLEMENTATION_GUIDE.md` - API reference

Then check logs:
```bash
tail -f storage/logs/laravel.log
grep -i "error\|image" storage/logs/laravel.log
```

