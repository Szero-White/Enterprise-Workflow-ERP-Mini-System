<?php

return [
    'hsts_enabled' => (bool) env('SECURITY_HSTS_ENABLED', false),
    'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
    'hsts_include_subdomains' => (bool) env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', false),
];
