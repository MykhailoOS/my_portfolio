# Portfolio Builder - Quick Start Guide

## 🚀 5-Minute Setup

### Prerequisites
- PHP 8.2+ with extensions: PDO, PDO_MySQL, ZIP, GD
- MySQL 8.x
- Apache with mod_rewrite OR Nginx

### Installation

```bash
# 1. Clone or download the project
cd /path/to/portfolio-builder

# 2. Run setup script
./setup.sh

# 3. Edit .env file
nano .env

# 4. Configure web server (see below)

# 5. Access in browser
http://localhost/
```

### Apache Configuration

**Option 1: Quick Setup (existing server)**

```bash
# Copy to your web root
cp -r public/* /var/www/html/portfolio/

# Access
http://localhost/portfolio/
```

**Option 2: Virtual Host**

```bash
sudo nano /etc/apache2/sites-available/portfolio.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName portfolio.local
    DocumentRoot /path/to/portfolio-builder/public
    
    <Directory /path/to/portfolio-builder/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable:
```bash
sudo a2ensite portfolio.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Add to `/etc/hosts`:
```
127.0.0.1   portfolio.local
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name portfolio.local;
    root /path/to/portfolio-builder/public;
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
}
```

## 📱 First Steps

### 1. Create a Project

1. Open the application
2. Click "Create Project"
3. Enter project name
4. Select languages (EN, UK, RU, PL)
5. Choose theme (Minimal, Bold, Creative)
6. Click "Create Project"

### 2. Add Content

1. Click on a block in the left sidebar
2. Edit content in the inspector (right side / bottom on mobile)
3. Use language tabs to add translations
4. Changes auto-save

### 3. Reorder Blocks

1. Drag blocks using the ☰ handle
2. On mobile: long-press (250ms) to start drag
3. Drop in new position
4. Order saves automatically

### 4. Export Portfolio

1. Click "Export ZIP" button
2. Wait for download
3. Extract ZIP file
4. Upload to any web hosting

## 🎨 Features Overview

### Blocks

- **Hero**: Main banner with photo, title, CTA
- **About**: Bio text and skills list
- **Projects**: Grid of project cards with images
- **Experience**: Work history timeline
- **Contact**: Email or contact form
- **Footer**: Social links and copyright

### Multilingual

- Edit content separately per language
- Export includes all selected languages
- Language switcher in exported site
- Fallback to English if translation missing

### Themes

- **Minimal**: Clean, white, professional
- **Bold**: Dark, high contrast, modern
- **Creative**: Colorful, unique, artistic

### Mobile

- Full editor on phone/tablet
- Touch-friendly drag-and-drop
- Bottom sheet inspector
- One-hand reachable controls
- Export works on mobile browsers

## 🐛 Troubleshooting

### "Cannot connect to database"

Edit `.env`:
```env
DB_HOST=localhost
DB_NAME=portfolio_builder
DB_USER=your_user
DB_PASS=your_password
```

### "Permission denied" on uploads

```bash
chmod 755 public/uploads
chown www-data:www-data public/uploads
```

### Blocks not draggable

Check browser console for JavaScript errors. Ensure SortableJS loaded:
```
/assets/js/vendor/sortable.min.js
```

### Export download fails

Check:
- ZIP extension: `php -m | grep zip`
- Temp directory: `ls -la /tmp`
- Browser pop-up blocker

## 📚 Learn More

- Full documentation: `README_SETUP.md`
- API reference: See `public/api.php`
- Database schema: `sql/schema.sql`

## 💡 Tips

1. **Start simple**: Use default content, then customize
2. **Mobile first**: Test on phone early
3. **Save often**: Changes auto-save, but export to backup
4. **Multilingual**: Add languages you need, skip others
5. **Themes**: Try all three, pick one, customize later

## 🎯 Example Workflow

```
Create Project
  ↓
Edit Hero (name, role, photo)
  ↓
Edit About (bio, skills)
  ↓
Add Projects (3-6 items)
  ↓
Add Contact (email)
  ↓
Preview
  ↓
Export ZIP
  ↓
Upload to hosting
  ↓
Share URL!
```

## 🌐 Hosting the Exported Site

The exported ZIP works on any static hosting:

- **GitHub Pages**: Free, easy
- **Netlify**: Free tier, drag-and-drop
- **Vercel**: Free, fast
- **Shared hosting**: Any PHP hosting
- **VPS**: Full control

### For Contact Form

Upload to **PHP hosting** (shared hosting, VPS) to enable form.

Otherwise, form falls back to `mailto:` link.

## ✨ Next Steps

1. Create your first project
2. Add your real content
3. Upload your photos
4. Translate to your languages
5. Export and deploy
6. Share your portfolio!

---

**Need help?** Check `README_SETUP.md` for detailed documentation.

**Found a bug?** Check browser console and PHP error logs.

**Want to customize?** All code is in `public/` and `lib/` directories.
