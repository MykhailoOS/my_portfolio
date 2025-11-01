# Portfolio Builder - Architecture

## Overview

Portfolio Builder is a mobile-first web application for creating multilingual portfolio websites with a visual drag-and-drop editor.

## Technology Stack

- **Backend**: PHP 8.2+ (procedural)
- **Frontend**: Vanilla JavaScript + jQuery 3.7.1
- **Database**: MySQL 8.x (InnoDB)
- **Libraries**: SortableJS 1.15.1 for drag-and-drop
- **Styling**: Custom CSS (mobile-first, responsive)

## Project Structure

```
portfolio-builder/       # Web root (DocumentRoot)
├── index.php           # Main builder UI
├── api.php             # REST API endpoints
├── .htaccess           # Apache rewrite rules
├── .env                # Environment variables (not in git)
├── assets/
│   ├── css/style.css
│   ├── js/
│   │   ├── app.js          # Main application logic
│   │   └── vendor/
│   │       └── sortable.min.js
│   └── img/
├── uploads/            # User-uploaded media
├── lib/                # Backend libraries
│   ├── config.php     # Environment configuration
│   ├── db.php         # Database functions
│   ├── utils.php      # Utility functions
│   └── export.php     # ZIP export logic
├── i18n/              # UI translations
│   ├── ui.en.json
│   ├── ui.uk.json
│   ├── ui.ru.json
│   └── ui.pl.json
└── sql/
    └── schema.sql     # Database schema
```

## Architecture Principles

### 1. Same-Origin Design

The application is designed to run as a **same-origin** application:
- Frontend and backend are served from the same domain
- All AJAX requests use relative URLs
- No CORS (Cross-Origin Resource Sharing) headers needed
- `crossDomain: false` is explicitly set in all AJAX calls

**Why no CORS?**
- CORS is only needed when frontend and backend are on different domains
- Same-origin requests are simpler, more secure, and faster
- Previous versions had unnecessary CORS complexity that has been removed

### 2. Mobile-First Approach

- All CSS uses mobile-first breakpoints (min-width)
- Touch-optimized UI components (drawers, bottom sheets)
- Minimum 44×44px tap targets
- Safe area insets for iOS notches
- Long-press drag on touch devices

### 3. Progressive Enhancement

- Core functionality works without JavaScript (form submissions)
- JavaScript enhances with auto-save, live preview, drag-and-drop
- Exported sites work without any build process

### 4. Security Layers

- **CSRF Protection**: Token validation on all state-changing requests
- **SQL Injection Prevention**: Prepared statements only
- **XSS Protection**: Output escaping with `htmlspecialchars()`
- **File Upload Security**: Type validation, size limits, SVG sanitization
- **Secure Headers**: X-Frame-Options, X-Content-Type-Options, etc.

## API Design

### Endpoint Pattern

All API requests go to `/api.php` with an `action` parameter:

```
POST /api.php?action=project.create
POST /api.php?action=block.update
GET  /api.php?action=project.get&id=123
```

### Response Format

All responses are JSON:

```json
{
  "id": 123,
  "name": "My Portfolio"
}
```

Errors:

```json
{
  "error": "Project not found"
}
```

### Actions

- `project.create` - Create new project
- `project.get` - Load project data
- `project.update` - Update project settings
- `block.add` - Add content block
- `block.update` - Update block data
- `block.reorder` - Change block order
- `block.delete` - Remove block
- `media.upload` - Upload image
- `export.zip` - Generate ZIP export

## Database Schema

### Tables

- **projects**: Project metadata (name, languages, theme)
- **pages**: Pages within projects (currently single-page only)
- **blocks**: Content blocks (type, order, data as JSON)
- **media**: Uploaded files (path, alt text, metadata)

### Data Storage

- **Structured data**: Stored in table columns (id, type, order)
- **Flexible data**: Stored as JSON in `data` column (multilingual content)
- **Benefits**: Schema flexibility without migrations for content changes

## Frontend Architecture

### App Object

Main application state and methods in `app.js`:

```javascript
const App = {
  project: null,      // Current project
  blocks: [],         // Block list
  selectedBlock: null,// Currently editing block
  currentLang: 'en',  // Preview language
  editorLang: 'en',   // UI language
  
  init() { ... },
  loadProject() { ... },
  renderUI() { ... },
  // ... more methods
};
```

### State Management

- LocalStorage for project ID persistence
- In-memory state for current editing session
- Auto-save debounced to 1 second
- Optimistic UI updates (update UI, then save)

### Event Handling

- Delegated event listeners for dynamic content
- jQuery for cross-browser consistency
- SortableJS for drag-and-drop

## Export System

### Process

1. Fetch project data from database
2. Generate HTML for each language
3. Copy assets (CSS, JS, images)
4. Create ZIP archive
5. Stream to browser as download

### Exported Structure

```
portfolio.zip
├── index.html        # Language selector
├── en/index.html     # English version
├── uk/index.html     # Ukrainian version
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── img/
└── contact.php       # If form enabled
```

## Performance

### Bundle Sizes
- JavaScript: ~55 KB gzipped
- CSS: ~8 KB gzipped
- Total: ~63 KB (excellent for interactive app)

### Optimization Techniques
- Minified vendor libraries
- CSS with mobile-first approach (less override code)
- Database indexes on foreign keys
- Efficient JSON storage

## Deployment

### Requirements
- PHP 8.2+ with extensions: PDO, PDO_MySQL, ZIP, GD
- MySQL 8.x
- Apache with mod_rewrite OR Nginx
- ~10 MB disk space

### Configuration
1. Set DocumentRoot to project root directory
2. Configure `.env` with database credentials
3. Import `sql/schema.sql`
4. Set permissions on `uploads/` (755)

### Web Server Setup

**Apache**: Uses `.htaccess` for URL rewriting
**Nginx**: Requires manual configuration (see QUICKSTART.md)

## Development Guidelines

### Code Style
- **PHP**: PSR-12 compliant, procedural style
- **JavaScript**: ES6+ features, semicolons, single quotes
- **CSS**: Mobile-first, BEM-like naming, logical properties

### Adding Features

1. **New Block Type**: 
   - Add to `$validTypes` in `api.php`
   - Add render method in `app.js`
   - Add translation keys in `i18n/*.json`

2. **New Language**:
   - Create `i18n/ui.XX.json`
   - Add checkbox in create project modal
   - Export automatically supports it

3. **New API Endpoint**:
   - Add case in `api.php` switch
   - Implement handler function
   - Add CSRF check if POST request

## Testing

See `TEST_CHECKLIST.md` for comprehensive testing guide.

## Common Issues

### "404 Not Found" on API Calls

**Cause**: mod_rewrite not enabled or .htaccess not processed

**Solution**:
```bash
# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check AllowOverride
# In Apache config: AllowOverride All
```

### Database Connection Errors

**Cause**: Wrong credentials in `.env`

**Solution**: Verify credentials, check MySQL service status

### Permission Denied on Upload

**Cause**: Web server doesn't have write access

**Solution**:
```bash
chmod 755 uploads
chown www-data:www-data uploads
```

## Security Considerations

### Production Deployment

1. **Change CSRF secret**: Regenerate session secret
2. **Restrict file uploads**: Consider user quotas
3. **Add authentication**: Implement user login system
4. **Use HTTPS**: Always use SSL in production
5. **Backup database**: Regular backups essential

### Known Limitations

- No user authentication (single user)
- No file upload quotas
- No rate limiting
- No CDN integration

## Future Enhancements

See `CHANGELOG.md` [Unreleased] section for planned features.

---

**Version**: 1.1.0  
**Last Updated**: 2024
