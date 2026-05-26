# DayTour Module - Implementation Guide

## Overview
A high-performance, production-ready DayTour module built with best practices for handling millions of rows. Includes Redis caching, AWS S3 image storage, and proper architectural patterns.

## Architecture

### Directory Structure
```
Modules/DayTour/
├── app/
│   ├── Actions/              # Business logic actions
│   │   ├── CreateDayTourAction.php
│   │   ├── UpdateDayTourAction.php
│   │   └── UploadDayTourImageAction.php
│   ├── Http/
│   │   ├── Controllers/      # Thin controllers
│   │   │   └── DayTourController.php
│   │   ├── Requests/         # Form request validation
│   │   │   ├── StoreDayTourRequest.php
│   │   │   ├── UpdateDayTourRequest.php
│   │   │   └── UploadDayTourImageRequest.php
│   │   └── Resources/        # API response resources
│   │       ├── DayTourResource.php
│   │       └── DayTourImageResource.php
│   ├── Models/              # Eloquent models
│   │   ├── DayTour.php
│   │   └── DayTourImage.php
│   ├── Repositories/        # Data access layer
│   │   └── DayTourRepository.php
│   └── Services/            # Business services
│       ├── S3ImageService.php
│       └── DayTourCacheService.php
├── database/
│   ├── migrations/
│   │   ├── 2026_05_02_000000_create_day_tours_table.php
│   │   └── 2026_05_02_000001_create_day_tour_images_table.php
│   └── factories/
│       └── DayTourFactory.php
├── routes/
│   └── api.php
└── config/
    └── config.php
```

## Database Schema

### day_tours Table
- **id** (ULID): Primary key, sortable unique identifier
- **agency_id** (UUID): References agency
- **city_id** (FK): References cities table
- **destination_id** (FK): References destinations table
- **title_translations** (JSONB): Multi-language titles
- **description_translations** (JSONB): Multi-language descriptions
- **is_active** (boolean): Toggle active/inactive
- **is_shared** (boolean): B2B sharing flag
- **Indexes**: Composite indexes for query optimization

### day_tour_images Table
- **id** (serial): Primary key
- **day_tour_id** (FK): References day_tours
- **s3_path** (text): S3 URL/path
- **is_primary** (boolean): Mark primary image
- **sort_order** (integer): Image ordering
- **Metadata**: filename, mime_type, file_size, disk
- **Indexes**: Performance optimized for millions of rows

## API Endpoints

### Day Tours
```
GET    /api/v1/day-tours                 # List all active day tours
POST   /api/v1/day-tours                 # Create new day tour
GET    /api/v1/day-tours/{id}            # Get specific day tour
PUT    /api/v1/day-tours/{id}            # Update day tour
DELETE /api/v1/day-tours/{id}            # Delete day tour
GET    /api/v1/day-tours/search          # Search with filters
```

### Images
```
POST   /api/v1/day-tours/{id}/images          # Upload image
GET    /api/v1/day-tours/{id}/images          # List images
DELETE /api/v1/day-tours/{id}/images/{imageId} # Delete image
```

## Request/Response Examples

### Create Day Tour
**Request:**
```json
{
  "agency_id": "550e8400-e29b-41d4-a716-446655440000",
  "city_id": 1,
  "destination_id": 5,
  "title_translations": [
    {"locale": "en", "value": "Mountain Adventure Tour"},
    {"locale": "ar", "value": "جولة المغامرة في الجبال"}
  ],
  "description_translations": [
    {"locale": "en", "value": "Experience the breathtaking mountain scenery..."},
    {"locale": "ar", "value": "اختبر المناظر الطبيعية الخلابة في الجبال..."}
  ],
  "is_active": true,
  "is_shared": false
}
```

**Response (201):**
```json
{
  "data": {
    "id": "01KQKPD51YMDPBDZQ5CN86TB3F",
    "agency_id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Mountain Adventure Tour",
    "description": "Experience the breathtaking mountain scenery...",
    "city": {
      "id": 1,
      "name": [{"locale": "en", "value": "Tbilisi"}]
    },
    "destination": {
      "id": 5,
      "name": [{"locale": "en", "value": "Caucasus Mountains"}]
    },
    "is_active": true,
    "is_shared": false,
    "primary_image": null,
    "images_count": 0,
    "created_at": "2026-05-02T09:29:58.000Z",
    "updated_at": "2026-05-02T09:29:58.000Z"
  }
}
```

### Upload Image
**Request:**
```
POST /api/v1/day-tours/{dayTourId}/images
Content-Type: multipart/form-data

- image: <file>
- is_primary: true (optional)
- sort_order: 0 (optional)
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "day_tour_id": "01KQKPD51YMDPBDZQ5CN86TB3F",
    "url": "https://s3.amazonaws.com/bucket/day-tours/01KQKPD51YMDPBDZQ5CN86TB3F/image_abc123.jpg",
    "filename": "mountain_view.jpg",
    "mime_type": "image/jpeg",
    "file_size": 2048576,
    "is_primary": true,
    "sort_order": 0,
    "disk": "s3",
    "created_at": "2026-05-02T09:29:58.000Z"
  }
}
```

## Usage Examples

### Create Day Tour
```php
$action = app(CreateDayTourAction::class);
$dayTour = $action->execute([
    'agency_id' => 'uuid-here',
    'city_id' => 1,
    'destination_id' => 5,
    'title_translations' => [...],
    'description_translations' => [...],
]);
```

### Query with Repository
```php
$repository = app(DayTourRepository::class);

// Get by agency
$tours = $repository->getByAgency($agencyId);

// Search with filters
$results = $repository->search([
    'agency_id' => $agencyId,
    'city_id' => $cityId,
    'search' => 'mountain',
]);

// Count
$total = $repository->count(['is_active' => true]);
```

### Caching
```php
$cache = app(DayTourCacheService::class);

// Get cached
$dayTour = $cache->get($dayTourId);

// Cache agency day tours
$tours = $cache->getAgencyDayTours($agencyId);

// Invalidate
$cache->forget($dayTourId);
```

### Image Upload
```php
$action = app(UploadDayTourImageAction::class);
$image = $action->execute($dayTour, $uploadedFile, $isPrimary);

// Batch upload
$images = $action->uploadBatch($dayTour, $files, true);
```

## Performance Considerations

### Indexing Strategy
- Composite indexes on (agency_id, is_active, created_at)
- Index on (city_id, is_active) for city filtering
- Index on (day_tour_id, is_primary) for image queries
- Indexed created_at for time-based queries

### Caching
- 1-hour TTL for individual day tours
- Cache invalidation on updates
- Separate caching for search results
- Redis backend recommended

### Optimization Tips
- Use pagination (15 items per page default)
- Load images relationship only when needed
- Use eager loading with `with()` in repository
- Enable query logging in production to monitor slow queries

## Configuration

Edit `Modules/DayTour/config/config.php`:

```php
's3' => [
    'disk' => 's3',                    // Storage disk
    'bucket' => 'tourism-app-bucket',  // S3 bucket
    'region' => 'us-east-1',           // AWS region
    'image_path' => 'day-tours',       // Path prefix
    'max_file_size' => 10240,          // Max 10MB
],

'cache' => [
    'enabled' => true,
    'ttl' => 3600,                     // 1 hour
    'prefix' => 'day_tour',
],
```

## Environment Variables

Add to `.env`:
```
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=tourism-app-bucket
FILESYSTEM_DISK=s3

DAYTOUR_CACHE_ENABLED=true
DAYTOUR_CACHE_TTL=3600
```

## Testing

Use the factory for tests:
```php
$dayTour = DayTourFactory::new()->create();
$inactive = DayTourFactory::new()->inactive()->create();
$shared = DayTourFactory::new()->shared()->create();
```

## Security

- Form request validation on all inputs
- File upload validation (mime types, size)
- Authorization middleware on API routes
- SQL injection prevention via Eloquent ORM
- CORS and rate limiting should be configured globally

## Future Enhancements

1. **Elasticsearch**: For advanced search on millions of records
2. **Queued Jobs**: For image processing (resizing, CDN optimization)
3. **Event Listeners**: For cache invalidation
4. **Rate Limiting**: Per-user/agency limits
5. **Webhook Support**: For external system integration
6. **Batch Operations**: Bulk create/update endpoints
7. **Soft Deletes**: Archive instead of delete (already implemented)

## Support

For issues or questions, refer to the main Laravel/DDD documentation.
