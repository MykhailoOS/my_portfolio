# 🎨 Portfolio Builder

A modern, **mobile-first** portfolio builder with multilingual support and ZIP export functionality. Create beautiful portfolio websites with a drag-and-drop interface that works seamlessly on phones, tablets, and desktops.

![Version](https://img.shields.io/badge/version-1.1.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![License](https://img.shields.io/badge/license-Proprietary-red)

> **📢 v1.1.0 Update**: Simplified architecture by removing unnecessary CORS headers and test files. The app uses same-origin requests and doesn't need CORS. See [FIXES_APPLIED.md](FIXES_APPLIED.md) for details.

## ✨ Features

### 🎯 Core Functionality
- **Visual Builder**: Drag-and-drop block editor with live preview
- **Multilingual**: Support for English, Ukrainian, Russian, and Polish
- **Theme System**: 3 pre-designed themes (Minimal, Bold, Creative)
- **ZIP Export**: Generate complete static website package
- **Auto-save**: Automatic content saving every second
- **Touch-Friendly**: Long-press drag on mobile devices

### 📱 Mobile Excellence (Signature Feature)
- **Fully mobile-operable builder** (create, edit, preview, export on phones)
- Responsive drawer/sheet UI for small screens
- 44×44px minimum tap targets
- Safe area inset support (iOS notch)
- One-hand usable controls
- Keyboard-aware scrolling
- Touch-optimized drag-and-drop

### 🧩 Content Blocks
- **Hero**: Banner with title, subtitle, CTA button, and avatar
- **About**: Bio section with rich text and skills list
- **Projects**: Grid layout with project cards, tags, and images
- **Experience**: Timeline of work history
- **Contact**: Email link or PHP contact form
- **Footer**: Social links and copyright notice

### 🌐 Multilingual System
- Per-language content storage
- Language switcher in exported sites
- Fallback to English if translation missing
- Easy addition of new languages

## 🚀 Quick Start

```bash
# 1. Clone repository
git clone <repository-url>
cd portfolio-builder

# 2. Run setup
./setup.sh

# 3. Configure database
cp .env.example .env
nano .env

# 4. Import schema
mysql -u root -p < sql/schema.sql

# 5. Configure web server (see QUICKSTART.md)

# 6. Open in browser
http://localhost/
```

**See [QUICKSTART.md](QUICKSTART.md) for detailed setup instructions.**

## 📋 Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 8.x
- **Web Server**: Apache (with mod_rewrite) or Nginx
- **PHP Extensions**:
  - PDO
  - PDO_MySQL
  - ZIP
  - GD or Imagick
  - JSON
- **Browser**: Modern browser with ES6+ support

## 📁 Project Structure

```
portfolio-builder/
├── public/              # Web root
│   ├── index.php       # Main builder UI
│   ├── api.php         # REST API endpoints
│   ├── .htaccess       # Apache configuration
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   └── vendor/
│   │   │       └── sortable.min.js
│   │   └── img/
│   └── uploads/        # User-uploaded media
├── lib/                # PHP libraries
│   ├── db.php         # Database functions
│   ├── utils.php      # Utility functions
│   ├── export.php     # ZIP export logic
│   └── config.php     # Configuration loader
├── i18n/              # UI translations
│   ├── ui.en.json
│   ├── ui.uk.json
│   ├── ui.ru.json
│   └── ui.pl.json
├── sql/
│   └── schema.sql     # Database schema
├── .env.example       # Environment template
├── .gitignore
├── setup.sh           # Setup script
├── README.md          # This file
├── QUICKSTART.md      # Quick start guide
└── README_SETUP.md    # Detailed documentation
```

## 🎨 Usage

### Creating a Project

1. Open the application in your browser
2. Click "Create Project"
3. Enter project name
4. Select languages to support
5. Choose a theme preset
6. Click "Create Project"

### Building Your Portfolio

1. **Add Content**: Click blocks in the sidebar to edit
2. **Translate**: Use language tabs to add content per language
3. **Upload Media**: Upload photos for hero, projects, etc.
4. **Reorder**: Drag blocks by the ☰ handle to reorder
5. **Preview**: See changes live in the canvas
6. **Export**: Click "Export ZIP" when ready

### Exporting

The exported ZIP contains:
```
portfolio.zip
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── img/
├── en/index.html
├── uk/index.html
├── ru/index.html
├── pl/index.html
├── contact.php (if form enabled)
└── README.txt
```

Upload to any web host to deploy your portfolio!

## 🔧 Configuration

### Database (.env)

```env
DB_HOST=localhost
DB_NAME=portfolio_builder
DB_USER=root
DB_PASS=your_password
```

### Upload Limits

Edit `public/.htaccess`:
```apache
php_value upload_max_filesize 5M
php_value post_max_size 50M
```

Or in `php.ini`:
```ini
upload_max_filesize = 5M
post_max_size = 50M
max_execution_time = 60
memory_limit = 256M
```

## 🔐 Security Features

- ✅ CSRF token protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (output escaping)
- ✅ File upload validation (type, size, content)
- ✅ SVG sanitization (removes scripts)
- ✅ HTML sanitization (whitelist tags)
- ✅ Secure headers (.htaccess)

## 📱 Mobile Support

### Tested Devices
- iPhone (Safari, Chrome)
- Android (Chrome, Firefox)
- iPad/Tablet (Safari, Chrome)

### Mobile Features
- Responsive breakpoints: 360, 480, 768, 1024, 1200px
- Touch-optimized drag-and-drop (SortableJS)
- Drawer navigation for sidebars
- Bottom sheet for inspector
- FAB (Floating Action Button) for quick actions
- Keyboard-aware input fields
- Safe area insets for notches

## 🌐 Adding Languages

### 1. Create Translation File

```bash
cp i18n/ui.en.json i18n/ui.fr.json
```

### 2. Translate Content

Edit `i18n/ui.fr.json` with French translations.

### 3. Update Default Languages (Optional)

In `public/assets/js/app.js`, add language checkbox in `showCreateProjectModal()`:

```javascript
<div class="form-check">
  <input type="checkbox" name="languages" value="fr" class="form-check-input">
  <label>FR</label>
</div>
```

### 4. Update Export

Language pages are generated automatically based on selected languages.

## 🎯 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api.php?action=project.create` | POST | Create new project |
| `/api.php?action=project.get` | GET | Get project data |
| `/api.php?action=project.update` | POST | Update project |
| `/api.php?action=block.add` | POST | Add block |
| `/api.php?action=block.update` | POST | Update block |
| `/api.php?action=block.reorder` | POST | Reorder blocks |
| `/api.php?action=block.delete` | POST | Delete block |
| `/api.php?action=media.upload` | POST | Upload media |
| `/api.php?action=export.zip` | POST | Export project |

All POST requests require CSRF token.

## 🧪 Testing

### Manual Testing Checklist

**Desktop:**
- [ ] Create project
- [ ] Add/edit/delete blocks
- [ ] Upload images
- [ ] Drag-and-drop reorder
- [ ] Multi-language content
- [ ] Theme selection
- [ ] Export ZIP
- [ ] Verify exported site

**Mobile (iOS/Android):**
- [ ] All desktop tests
- [ ] Touch drag-and-drop
- [ ] Drawer/sheet navigation
- [ ] Keyboard behavior
- [ ] Image upload (camera)
- [ ] Export download

## 🐛 Troubleshooting

### Common Issues

- **Database connection errors**: Check `.env` credentials and MySQL service status
- **Permission problems**: Ensure web server has write access to `public/uploads/`
- **Upload failures**: Check PHP `upload_max_filesize` and `post_max_size` settings
- **Export errors**: Verify PHP ZIP extension is installed and `memory_limit` is sufficient
- **404 errors**: Ensure `.htaccess` is uploaded and mod_rewrite is enabled
- **Mobile layout issues**: Clear browser cache and check viewport meta tag

See [README_SETUP.md](README_SETUP.md#troubleshooting) for detailed solutions.

## 📊 Performance

### Bundle Sizes
- **JavaScript**: ~55 KB gzipped (jQuery + SortableJS + App)
- **CSS**: ~8 KB gzipped
- **Total**: ~63 KB (well under 150 KB budget)

### Optimization
- Server-side rendering for export
- Database indexes on foreign keys
- JSON storage for flexible data
- Lazy loading thumbnails
- Minified vendor libraries

## 🛠️ Development

### Tech Stack
- **Backend**: PHP 8.2+ (procedural)
- **Frontend**: Vanilla JavaScript + jQuery 3.7
- **Database**: MySQL 8.x (InnoDB)
- **Libraries**: SortableJS 1.15
- **Styling**: Custom CSS (mobile-first)

### Code Style
- PHP: PSR-12 compliant
- JavaScript: ES6+ features
- CSS: Mobile-first, BEM-like naming
- SQL: Prepared statements only

### Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly (desktop + mobile)
5. Submit pull request

## 📜 License

Proprietary - All rights reserved.

## 🙏 Acknowledgments

- [SortableJS](https://sortablejs.github.io/Sortable/) - Touch-friendly drag-and-drop
- [jQuery](https://jquery.com/) - DOM manipulation
- Modern CSS techniques - Mobile-first responsive design

## 📞 Support

- **Quick Start**: See [QUICKSTART.md](QUICKSTART.md) for 5-minute setup
- **Architecture**: See [ARCHITECTURE.md](ARCHITECTURE.md) for technical details
- **Testing**: See [TEST_CHECKLIST.md](TEST_CHECKLIST.md) for verification
- **Detailed Setup**: See [README_SETUP.md](README_SETUP.md) for comprehensive guide
- **Recent Fixes**: See [FIXES_APPLIED.md](FIXES_APPLIED.md) for v1.1.0 changes

## 🗺️ Roadmap

### Planned Features
- [ ] User authentication system
- [ ] Project templates
- [ ] More block types (gallery, testimonials, blog)
- [ ] Custom font upload
- [ ] Advanced theme customization
- [ ] Email export link (mobile fallback)
- [ ] Project sharing/collaboration
- [ ] Version history
- [ ] A/B testing for exported sites

### Under Consideration
- [ ] React/Vue rewrite for builder (keeping PHP backend)
- [ ] Real-time collaboration
- [ ] Analytics integration (optional)
- [ ] Custom domain management
- [ ] CDN integration for exported assets

---

**Version**: 1.1.0  
**Last Updated**: November 2024  
**Built with**: ❤️ and ☕

**Get started**: Run `./setup.sh` and open in your browser!
