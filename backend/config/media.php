<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Media Upload Pipeline
|--------------------------------------------------------------------------
| Single source of truth for the shared Media Service. Every business module
| uploads through this pipeline — validation rules, storage disk selection,
| image limits and thumbnailing are configured here, never per-module.
|
| Storage is driven entirely by Laravel filesystem disks, so adding S3, R2,
| Wasabi, GCS or Azure later is a config change — no business code changes.
*/

return [
    // Disk used per visibility. Private files live on the non-public disk
    // (outside the web root); public files on the web-served disk.
    'disks' => [
        'private' => env('MEDIA_PRIVATE_DISK', 'local'),
        'public' => env('MEDIA_PUBLIC_DISK', 'public'),
    ],

    // Default visibility for new uploads when the caller does not specify one.
    'default_visibility' => 'private',

    // Hard ceiling applied to every upload regardless of category (in KB).
    'max_size_kb' => (int) env('MEDIA_MAX_SIZE_KB', 20480),

    // Allowed categories. A file's category is resolved by its extension; the
    // detected MIME type must then be consistent with the category.
    'categories' => [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size_kb' => 5120,
        ],
        'document' => [
            'extensions' => ['pdf', 'docx', 'xlsx', 'pptx'],
            'mimes' => [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                // Office Open XML files are ZIP containers; finfo often reports this.
                'application/zip',
            ],
            'max_size_kb' => 10240,
        ],
        'archive' => [
            'extensions' => ['zip'],
            'mimes' => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
            'max_size_kb' => 20480,
        ],
    ],

    // Extensions that may NEVER be uploaded (executables / scripts), checked
    // before anything else as a hard security gate.
    'blocked_extensions' => [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'exe', 'sh', 'bash',
        'bat', 'cmd', 'com', 'cgi', 'pl', 'py', 'rb', 'js', 'jar', 'msi',
        'dll', 'so', 'bin', 'htaccess', 'htm', 'html', 'svg',
    ],

    'image' => [
        'max_width' => (int) env('MEDIA_IMAGE_MAX_WIDTH', 6000),
        'max_height' => (int) env('MEDIA_IMAGE_MAX_HEIGHT', 6000),

        // Derivative generation is enabled per category. Originals are NEVER
        // modified — every derivative is written to its own location.
        'derivatives_enabled' => true,

        // Dedicated directory (relative to the disk root) for generated images.
        'derivatives_directory' => 'derivatives',

        // Configurable image sizes. Add 'medium'/'large' here and they are
        // generated automatically — no code change required.
        'sizes' => [
            'thumb' => ['width' => 320, 'height' => 320],
            // 'medium' => ['width' => 768, 'height' => 768],
            // 'large' => ['width' => 1600, 'height' => 1600],
        ],
    ],

    // TTL (minutes) for temporary signed URLs on drivers that support them.
    'temporary_url_ttl' => 15,

    // Reference registry — tables/columns that may point at a media id. Checked
    // before deletion so a referenced file is never orphaned. New modules add
    // their reference columns here; the Media Service does the rest.
    'references' => [
        ['table' => 'students', 'columns' => ['photo_media_id']],
        ['table' => 'guardians', 'columns' => ['photo_media_id']],
        ['table' => 'admission_documents', 'columns' => ['media_id']],
        ['table' => 'school_branding', 'columns' => [
            'logo_media_id', 'logo_dark_media_id', 'favicon_media_id',
            'login_background_media_id', 'principal_signature_media_id',
            'stamp_media_id', 'report_logo_media_id', 'receipt_logo_media_id',
            'id_card_media_id',
        ]],
    ],
];
