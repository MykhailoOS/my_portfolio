# Portfolio Builder - Setup & Documentation

## 🎯 Overview

A modern, mobile-first portfolio builder with multilingual support (EN, UK, RU, PL) and ZIP export functionality. Built with PHP 8.2+, jQuery, and SortableJS.

## 📋 Requirements

- PHP 8.2 or higher
- MySQL 8.x
- Apache/Nginx with mod_rewrite enabled
- Extensions: PDO, PDO_MySQL, ZIP, GD or Imagick
- Composer (optional, for dependencies)

## 🚀 Installation

### 1. Database Setup

```bash
# Create database and import schema
mysql -u root -p < sql/schema.sql
```

Or manually:
```sql
CREATE DATABASE portfolio_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_builder;
SOURCE sql/schema.sql;
```

### 2. Environment Configuration

Create a `.env` file in the project root (optional):

```env
DB_HOST=localhost
DB_NAME=portfolio_builder
DB_USER=root
DB_PASS=your_password
```

Or set environment variables directly in your web server configuration.

### 3. File Permissions

```bash
# Make uploads directory writable
chmod 755 public/uploads
chown www-data:www-data public/uploads

# Ensure temp directory is writable
chmod 1777 /tmp
```

### 4. Web Server Configuration

#### Apache

The `.htaccess` file is already configured. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Apache VirtualHost example:
```apache
<VirtualHost *:80>
    ServerName portfolio.local
    DocumentRoot /path/to/project/public
    
    <Directory /path/to/project/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/portfolio_error.log
    CustomLog ${APACHE_LOG_DIR}/portfolio_access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name portfolio.local;
    root /path/to/project/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

### 5. Access the Application

Navigate to `http://portfolio.local` or your configured domain.

## 📱 Mobile Features

### Touch-Friendly Design

- **Minimum tap target**: 44×44 pixels
- **Long-press drag**: 250ms delay for SortableJS
- **Responsive breakpoints**: 360, 480, 768, 1024, 1200px
- **Safe area insets**: iOS notch and bottom bar support

### Mobile Layout

- **< 768px**: Sidebar becomes left drawer
- **< 1024px**: Inspector becomes bottom sheet
- **FAB (Floating Action Button)**: Quick access on mobile

### Testing on Mobile

1. Use Chrome DevTools Device Mode
2. Test on real devices (iOS Safari, Android Chrome)
3. Check touch interactions (drag, swipe, tap)
4. Verify keyboard behavior (focus, scroll)

## 🌐 Multilingual System

### Adding a New Language

1. **Create translation file**:
```bash
cp i18n/ui.en.json i18n/ui.de.json
```

2. **Translate all keys** in the new file

3. **Update default languages** (optional):
Modify the language checkboxes in `public/assets/js/app.js` in the `showCreateProjectModal` function.

### Translation Structure

```json
{
  "app_title": "Portfolio Builder",
  "create_project": "Create Project",
  ...
}
```

### Content Languages

Each block stores content per language:

```json
{
  "en": {
    "title": "My Title",
    "subtitle": "My Subtitle"
  },
  "uk": {
    "title": "Мій Заголовок",
    "subtitle": "Мій Підзаголовок"
  }
}
```

## 🎨 Theme System

### Available Presets

- **Minimal**: Clean, white background, subtle colors
- **Bold**: Dark background, vibrant accents
- **Creative**: Light gray, purple accents

### Theme Structure

```json
{
  "preset": "minimal",
  "colors": {
    "primary": "#0066cc",
    "accent": "#ff3366"
  },
  "fontScale": "md",
  "spacingScale": "md"
}
```

### Customizing Themes

Edit `lib/export.php` in the `generateCSS()` method to add new presets or modify existing ones.

## 🧩 Block Types

### Available Blocks

1. **Hero**: Main banner with title, subtitle, CTA
2. **About**: Bio section with skills
3. **Projects**: Grid of project cards
4. **Experience**: Timeline of work history
5. **Contact**: Email link or contact form
6. **Footer**: Social links and copyright

### Adding a New Block Type

1. **Update database schema** (add to ENUM in blocks.type):
```sql
ALTER TABLE blocks MODIFY COLUMN type ENUM('hero', 'about', 'projects', 'experience', 'contact', 'footer', 'new_type');
```

2. **Add translation keys** in all `i18n/ui.*.json`:
```json
{
  "block_new_type": "New Block"
}
```

3. **Update API** (`public/api.php`):
- Add to `$validTypes` array in `handleBlockAdd()`

4. **Add renderer** in `lib/export.php`:
```php
private function renderNewType($data, $lang) {
    return '<section>...</section>';
}
```

5. **Add inspector fields** in `public/assets/js/app.js`:
```javascript
case 'new_type':
  html += `
    <div class="form-group">
      <label>${this.t('field_label')}</label>
      <input type="text" class="form-control" data-field="fieldName">
    </div>
  `;
  break;
```

## 📤 Export System

### How It Works

1. User clicks "Export ZIP"
2. Server generates static HTML per language
3. Copies media files to export directory
4. Creates CSS and JS files
5. Generates `contact.php` if form enabled
6. Packages everything into a ZIP
7. Logs export in database
8. Streams ZIP to browser
9. Cleans up temporary files

### Export Structure

```
portfolio.zip
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── img/
│       ├── image1.jpg
│       └── image2.png
├── en/index.html
├── uk/index.html
├── ru/index.html
├── pl/index.html
├── contact.php (optional)
└── README.txt
```

### Troubleshooting Export

**Issue**: ZIP download fails on mobile

**Solution**: The app includes automatic download. If it fails, the user should:
1. Check browser pop-up settings
2. Try on desktop browser
3. Use the email fallback (future feature)

**Issue**: PHP mail() not working

**Solution**: Configure SMTP or use `mailto:` link fallback:
```php
// In exported contact.php, add fallback
if (!@mail(...)) {
    header("Location: mailto:email@example.com");
}
```

## 🔐 Security

### Implemented Measures

1. **CSRF Protection**: Token validation on state-changing requests
2. **SQL Injection**: Prepared statements with PDO
3. **XSS Prevention**: `htmlspecialchars()` on all output
4. **File Upload Validation**:
   - Type whitelist (JPEG, PNG, WebP, SVG)
   - Size limit (5MB per file, 50MB per project)
   - SVG sanitization (removes scripts)
5. **HTML Sanitization**: Allowlist for rich text

### Additional Recommendations

1. **Use HTTPS** in production
2. **Set secure headers** (already in .htaccess)
3. **Regular updates** of PHP and dependencies
4. **Backup database** regularly
5. **Limit upload directory** permissions
6. **Use rate limiting** for API endpoints (not implemented)

## 🐛 Troubleshooting

### Common Issues

**1. Database connection error**

Check:
- MySQL is running: `sudo systemctl status mysql`
- Credentials are correct in environment variables
- Database exists: `mysql -u root -p -e "SHOW DATABASES;"`

**2. Blocks not draggable**

Check:
- SortableJS is loaded: Check browser console
- Touch events enabled: Test on device, not emulator

**3. Images not uploading**

Check:
- Upload directory is writable: `ls -la public/uploads`
- PHP upload settings: `php -i | grep upload`
- File size limits in php.ini

**4. Export fails**

Check:
- ZIP extension: `php -m | grep zip`
- Temp directory writable: `ls -la /tmp`
- PHP memory limit: `php -i | grep memory_limit`

**5. Mobile layout broken**

Check:
- Viewport meta tag present
- CSS loaded correctly
- Browser cache (hard refresh)

## 📊 Performance

### Frontend Budget

- **JavaScript**: ≤ 150 KB gzipped
  - jQuery: ~30 KB
  - SortableJS: ~10 KB
  - App: ~15 KB
  - Total: ~55 KB ✅

- **CSS**: ≤ 50 KB gzipped
  - Main styles: ~8 KB ✅

### Backend Optimization

- Database indexes on foreign keys and frequently queried fields
- JSON field storage for flexible data
- Lazy loading of media thumbnails (to implement)
- Server-side pagination for large projects (to implement)

## 🧪 Testing Checklist

### Desktop

- [ ] Create project
- [ ] Add/edit/delete blocks
- [ ] Drag-and-drop reorder
- [ ] Upload images
- [ ] Switch languages
- [ ] Edit content per language
- [ ] Change theme
- [ ] Preview changes
- [ ] Export ZIP
- [ ] Extract and open exported site

### Mobile (iOS)

- [ ] Open in Safari
- [ ] Create project (modal works)
- [ ] Open sidebar drawer (swipe/button)
- [ ] Select block
- [ ] Open inspector bottom sheet
- [ ] Edit text fields (keyboard doesn't cover)
- [ ] Long-press drag blocks
- [ ] Upload image (camera/gallery)
- [ ] Export ZIP (download works)

### Mobile (Android)

- [ ] Open in Chrome
- [ ] All iOS checks above
- [ ] Test back button behavior
- [ ] Test hardware keyboard (if available)

## 📝 Configuration Reference

### PHP Settings (php.ini)

```ini
upload_max_filesize = 5M
post_max_size = 50M
max_execution_time = 60
memory_limit = 256M
```

### Database Tuning

```sql
-- For InnoDB optimization
SET GLOBAL innodb_buffer_pool_size = 256M;
SET GLOBAL innodb_log_file_size = 128M;
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | localhost | MySQL host |
| `DB_NAME` | portfolio_builder | Database name |
| `DB_USER` | root | Database user |
| `DB_PASS` | (empty) | Database password |

## 🔄 Backup & Restore

### Backup

```bash
# Database
mysqldump -u root -p portfolio_builder > backup.sql

# Uploads
tar -czf uploads-backup.tar.gz public/uploads/
```

### Restore

```bash
# Database
mysql -u root -p portfolio_builder < backup.sql

# Uploads
tar -xzf uploads-backup.tar.gz -C public/
```

## 📞 Support

For issues or questions:
1. Check this documentation
2. Review code comments
3. Check browser console for JavaScript errors
4. Check PHP error logs: `tail -f /var/log/apache2/error.log`

## 🎓 Development Tips

### Adding Features

1. **Backend**: Add API endpoint in `public/api.php`
2. **Frontend**: Add handler in `public/assets/js/app.js`
3. **UI**: Update HTML in `public/index.php` or render in JS
4. **Styles**: Add CSS in `public/assets/css/style.css`
5. **Translations**: Update all `i18n/ui.*.json` files

### Code Style

- PHP: Follow PSR-12 standards
- JavaScript: ES6+ features, jQuery for DOM
- CSS: Mobile-first, use CSS variables
- SQL: Use prepared statements, never raw queries

### Mobile Testing Commands

```bash
# Simulate touch events in Chrome DevTools
# Settings > Sensors > Touch: Force enabled

# Test on local network
# Get local IP
ip addr show | grep inet

# Access from phone
http://192.168.1.x/
```

## 📜 License

Proprietary - All rights reserved

---

**Version**: 1.0.0  
**Last Updated**: 2024  
**Author**: Portfolio Builder Team
