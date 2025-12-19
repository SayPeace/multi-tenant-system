# Multi-Tenant Journal Management System

## Setup Guide

This document provides step-by-step instructions for setting up the Multi-Tenant Journal Management System using the **Distributed Frontend + Central API** architecture.

---

## Architecture Overview

```
┌─────────────────┐
│ journalA.com    │──→ [Frontend A] ──┐
├─────────────────┤                   │
│ journalB.org    │──→ [Frontend B] ──┼──→ [Central API] ──→ [MySQL DB]
├─────────────────┤                   │    (api.system.io)
│ journalC.edu    │──→ [Frontend C] ──┘
└─────────────────┘
   (Lightweight        (Returns JSON
    PHP sites)          with tenant data)
```

### Components

1. **Central API** (`/api`) - Single backend serving all journals
2. **Frontend Template** (`/frontend-template`) - Template for journal frontends
3. **Journal Frontends** (`/journals/*`) - Individual journal sites
4. **Core** (`/core`) - Shared PHP classes

---

## Prerequisites

- PHP 7.4+ (PHP 8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled (or nginx)
- cURL extension enabled

---

## Installation Steps

### 1. Database Setup

1. Create the database:
```sql
CREATE DATABASE multi_tenant_journals CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the schema:
```bash
mysql -u root -p multi_tenant_journals < database/schema.sql
```

This creates all tables with sample data for testing.

### 2. Configure Database Connection

Edit `config/database.php`:

```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'multi_tenant_journals',
    'username' => 'your_username',
    'password' => 'your_password',
    // ...
];
```

Or use environment variables:
```bash
export DB_HOST=localhost
export DB_NAME=multi_tenant_journals
export DB_USER=your_username
export DB_PASS=your_password
```

### 3. Configure Apache Virtual Hosts (Production)

For each journal, create a virtual host pointing to its frontend:

```apache
# Journal A
<VirtualHost *:80>
    ServerName journalA.com
    DocumentRoot /var/www/journals/journal-a

    <Directory /var/www/journals/journal-a>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Central API
<VirtualHost *:80>
    ServerName api.yoursystem.io
    DocumentRoot /var/www/multi-tenant-system/api

    <Directory /var/www/multi-tenant-system/api>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 4. Test with XAMPP (Development)

For local testing with XAMPP:

1. Access the API: `http://localhost/multi-tenant-system/api/health`
2. Access sample journal: `http://localhost/multi-tenant-system/journals/sample-journal/`

---

## API Reference

### Authentication

All API requests (except `/health` and `/info`) require an API key:

```bash
curl -H "X-API-Key: your_api_key_here" \
     http://localhost/multi-tenant-system/api/tenant
```

### Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/health` | GET | Health check (no auth) |
| `/info` | GET | API info (no auth) |
| `/tenant` | GET | Get tenant information |
| `/articles` | GET | List articles (paginated) |
| `/articles/recent` | GET | Get recent articles |
| `/articles/{slug}` | GET | Get single article |
| `/volumes` | GET | List volumes |
| `/volumes/with-issues` | GET | Volumes with their issues |
| `/issues` | GET | List issues |
| `/issues/current` | GET | Current issue with articles |
| `/editorial-board` | GET | Editorial board members |
| `/pages` | GET | CMS pages |
| `/pages/{slug}` | GET | Single page |
| `/announcements` | GET | Journal announcements |
| `/search?q={query}` | GET | Search articles |

### Example Response

```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1,
        "slug": "journal-science",
        "name": "International Journal of Science",
        "branding": {
            "logo_url": null,
            "primary_color": "#1a73e8"
        }
    }
}
```

---

## Creating a New Journal

### 1. Add Tenant to Database

```sql
INSERT INTO tenants (slug, name, subdomain, api_key, email) VALUES
('my-journal', 'My New Journal', 'myjournal.yoursystem.io',
 'api_key_myjournal_12345678901234567890123456', 'editor@myjournal.org');
```

### 2. Create Frontend Directory

```bash
cp -r frontend-template journals/my-journal
```

### 3. Configure the Frontend

Edit `journals/my-journal/config.php`:

```php
return [
    'api_url' => 'http://api.yoursystem.io',
    'api_key' => 'api_key_myjournal_12345678901234567890123456',
    // ...
];
```

### 4. Customize Theme (Optional)

Copy the default theme and customize:

```bash
cp -r frontend-template/themes/default journals/my-journal/themes/custom
```

Update `config.php`:
```php
'theme' => 'custom',
```

---

## Sample API Keys (for Testing)

| Journal | API Key |
|---------|---------|
| Science Journal | `api_key_science_12345678901234567890123456789012` |
| Medical Journal | `api_key_medicine_123456789012345678901234567890` |
| Tech Journal | `api_key_tech_1234567890123456789012345678901234` |

---

## Directory Structure

```
multi-tenant-system/
├── api/                      # Central API
│   ├── endpoints/            # API endpoint handlers
│   ├── middleware/           # Authentication, etc.
│   ├── index.php             # API entry point
│   └── .htaccess
├── core/                     # Shared PHP classes
│   ├── Models/               # Data models
│   ├── Database.php          # Database connection
│   ├── Tenant.php            # Tenant management
│   ├── TenantAwareModel.php  # Base model with tenant isolation
│   └── Response.php          # API responses
├── config/                   # Configuration files
│   ├── database.php
│   └── api.php
├── database/                 # Database files
│   └── schema.sql            # Full schema with sample data
├── frontend-template/        # Template for journal frontends
│   ├── themes/default/       # Default theme files
│   ├── ApiClient.php         # API communication
│   ├── Cache.php             # Response caching
│   ├── config.php            # Template config
│   └── index.php             # Frontend entry point
├── journals/                 # Individual journal frontends
│   └── sample-journal/       # Example journal
├── SETUP.md                  # This file
└── README.md                 # Original requirements
```

---

## Security Considerations

1. **API Keys**: Store securely, rotate periodically
2. **HTTPS**: Use SSL in production
3. **Input Validation**: All inputs are validated
4. **SQL Injection**: Using prepared statements
5. **Tenant Isolation**: Enforced at model level

---

## Caching

The frontend uses file-based caching by default:

- Tenant info: 1 hour
- Menu pages: 1 hour
- Article lists: 5 minutes
- Single articles: 1 hour

Clear cache:
```php
$cache->clear();
```

---

## Troubleshooting

### API returns 401 Unauthorized
- Check if API key is correct
- Verify the tenant is active in database

### Frontend shows "Failed to connect to API"
- Check API URL in config.php
- Verify API is accessible
- Check PHP cURL extension is enabled

### Pages not loading (404)
- Ensure mod_rewrite is enabled
- Check .htaccess files exist
- Verify AllowOverride is set to All

---

## Production Checklist

- [ ] Update database credentials
- [ ] Set debug mode to false
- [ ] Configure SSL certificates
- [ ] Set up proper virtual hosts
- [ ] Configure backup system
- [ ] Set up monitoring
- [ ] Test all endpoints
- [ ] Configure CDN (optional)

---

## Support

For issues or questions, refer to the README.md for architectural decisions and the original requirements.
