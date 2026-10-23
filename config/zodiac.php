<?php

return [
    // Shown on the public pages (privacy, terms, support, delete account).
    // Keep minimum_age in sync with AppConfig.minimumAge in the mobile app.
    'minimum_age' => (int) env('MINIMUM_AGE', 16),

    'support_email' => env('SUPPORT_EMAIL', 'support@example.com'),

    'company_name' => env('COMPANY_NAME', env('APP_NAME', 'Zodiac Blend')),
];
