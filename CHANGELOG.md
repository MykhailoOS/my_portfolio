# Changelog

All notable changes to Portfolio Builder will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-01

### Added
- Initial release of Portfolio Builder
- Mobile-first responsive builder interface
- Support for 4 languages (EN, UK, RU, PL)
- 6 content block types (Hero, About, Projects, Experience, Contact, Footer)
- 3 theme presets (Minimal, Bold, Creative)
- Drag-and-drop block reordering with SortableJS
- Touch-friendly mobile interface with drawers and bottom sheets
- Auto-save functionality
- ZIP export with static HTML generation
- Media upload with image validation
- CSRF protection
- SQL injection prevention
- XSS protection
- File upload security
- Multilingual content editing
- Live preview canvas
- Language switcher in exported sites
- Contact form PHP handler
- Setup script for easy installation
- Comprehensive documentation

### Security
- CSRF token validation on all state-changing requests
- Prepared statements for all database queries
- HTML output escaping
- File upload type and size validation
- SVG sanitization to prevent script injection
- Secure headers in .htaccess

### Performance
- Total bundle size: ~63 KB gzipped (JS + CSS)
- Database indexes on foreign keys
- Efficient JSON storage for flexible content
- Optimized CSS with mobile-first approach

## [1.1.0] - 2024

### Changed
- **Simplified Architecture**: Removed unnecessary CORS headers since application uses same-origin requests
  - Removed redundant CORS configuration from multiple files
  - Removed test utilities that added unnecessary complexity
  - Consolidated documentation
  - Improved error handling clarity

## [Unreleased]

### Planned
- User authentication system
- Project templates
- More block types (gallery, testimonials)
- Custom font upload
- Advanced theme customization
- Email export link for mobile
- Version history
- Project sharing

---

## Version History

### Version Numbering

- **MAJOR** version: Incompatible API changes
- **MINOR** version: Backward-compatible functionality
- **PATCH** version: Backward-compatible bug fixes

### Support Policy

- Latest version: Full support
- Previous minor: Security updates only
- Older versions: No support
