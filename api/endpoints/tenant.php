<?php
/**
 * Tenant API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /tenant - Get current tenant information
 */

use Core\Tenant;
use Core\Response;

$tenant = Tenant::current();

Response::success([
    'id' => $tenant->id,
    'slug' => $tenant->slug,
    'name' => $tenant->name,
    'tagline' => $tenant->tagline,
    'description' => $tenant->description,
    'domain' => $tenant->subdomain,
    'custom_domain' => $tenant->custom_domain,
    'branding' => [
        'logo_url' => $tenant->logo_url,
        'favicon_url' => $tenant->favicon_url,
        'primary_color' => $tenant->primary_color,
        'secondary_color' => $tenant->secondary_color,
        'theme' => $tenant->theme
    ],
    'contact' => [
        'email' => $tenant->email,
        'phone' => $tenant->phone,
        'address' => $tenant->address
    ]
]);
