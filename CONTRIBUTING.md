# Contributing to Portfolio Builder

Thank you for your interest in contributing to Portfolio Builder! This document provides guidelines for contributing to the project.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)

## Code of Conduct

### Our Standards

- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

## Getting Started

### Prerequisites

- PHP 8.2+
- MySQL 8.x
- Node.js 16+ (for development tools, optional)
- Git

### Setup Development Environment

```bash
# Clone the repository
git clone <repository-url>
cd portfolio-builder

# Run setup
./setup.sh

# Configure environment
cp .env.example .env
nano .env

# Import database
mysql -u root -p < sql/schema.sql

# Start development server
php -S localhost:8000 -t public
```

## Development Workflow

### 1. Create a Branch

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/issue-description
```

Branch naming conventions:
- `feature/` - New features
- `fix/` - Bug fixes
- `docs/` - Documentation changes
- `refactor/` - Code refactoring
- `test/` - Adding tests

### 2. Make Changes

- Write clean, readable code
- Follow existing code style
- Add comments for complex logic
- Update documentation as needed

### 3. Test Your Changes

```bash
# Test on desktop browsers
# - Chrome
# - Firefox
# - Safari
# - Edge

# Test on mobile devices
# - iOS Safari
# - Android Chrome
```

### 4. Commit

```bash
git add .
git commit -m "feat: add new feature description"
```

Commit message format:
```
<type>: <subject>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Formatting
- `refactor`: Code restructuring
- `test`: Adding tests
- `chore`: Maintenance

## Coding Standards

### PHP (PSR-12)

```php
<?php

// Use strict types
declare(strict_types=1);

// Function naming: camelCase
function getUserById(int $id): ?array {
    // Implementation
}

// Classes: PascalCase
class ProjectExporter {
    // Properties: camelCase
    private string $exportPath;
    
    // Methods: camelCase
    public function export(): string {
        // Implementation
    }
}

// Constants: UPPER_SNAKE_CASE
const MAX_UPLOAD_SIZE = 5242880;
```

### JavaScript (ES6+)

```javascript
// Use const/let, not var
const App = {
  // Property names: camelCase
  currentProject: null,
  
  // Method names: camelCase
  loadProject: function(id) {
    // Implementation
  },
  
  // Private methods: prefix with _
  _validateInput: function(data) {
    // Implementation
  }
};

// Constants: UPPER_SNAKE_CASE
const MAX_FILE_SIZE = 5242880;

// Functions: camelCase
function formatDate(date) {
  // Implementation
}
```

### CSS

```css
/* Use mobile-first approach */
.component {
  /* Mobile styles first */
  padding: 1rem;
}

@media (min-width: 768px) {
  .component {
    /* Desktop styles */
    padding: 2rem;
  }
}

/* Naming: kebab-case */
.block-item {}
.block-item--selected {}
.block-item__handle {}

/* Use CSS variables */
:root {
  --color-primary: #0066cc;
}

.component {
  color: var(--color-primary);
}
```

### SQL

```sql
-- Table names: lowercase, plural
CREATE TABLE projects (...);

-- Column names: snake_case
CREATE TABLE projects (
    id INT UNSIGNED,
    created_at TIMESTAMP
);

-- Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
```

## File Organization

```
feature-name/
├── public/
│   ├── api.php          (add new endpoint)
│   ├── assets/
│   │   ├── js/app.js   (add JS logic)
│   │   └── css/style.css (add styles)
├── lib/
│   └── feature.php      (add backend logic)
├── i18n/
│   └── ui.*.json        (add translations)
└── sql/
    └── migration-*.sql  (add DB changes)
```

## Testing

### Manual Testing Checklist

**Desktop:**
- [ ] Feature works in Chrome
- [ ] Feature works in Firefox
- [ ] Feature works in Safari
- [ ] Feature works in Edge
- [ ] No console errors
- [ ] No PHP errors in logs

**Mobile:**
- [ ] Feature works on iOS Safari
- [ ] Feature works on Android Chrome
- [ ] Touch interactions work correctly
- [ ] No layout issues
- [ ] Keyboard behavior correct

**Database:**
- [ ] Migrations run successfully
- [ ] No data corruption
- [ ] Proper indexes
- [ ] Foreign key constraints work

**Security:**
- [ ] CSRF token validation
- [ ] Input sanitization
- [ ] Output escaping
- [ ] SQL injection prevention
- [ ] File upload validation

## Submitting Changes

### Pull Request Process

1. **Update Documentation**
   - Update README.md if needed
   - Add entry to CHANGELOG.md
   - Update code comments

2. **Create Pull Request**
   - Use clear, descriptive title
   - Describe changes in detail
   - Reference related issues
   - Include screenshots for UI changes

3. **PR Template**

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Desktop browsers tested
- [ ] Mobile devices tested
- [ ] Edge cases considered

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-reviewed code
- [ ] Commented complex code
- [ ] Updated documentation
- [ ] No new warnings
- [ ] Added tests (if applicable)

## Screenshots
(if applicable)
```

4. **Review Process**
   - Address review comments
   - Make requested changes
   - Re-request review

5. **Merge**
   - Squash and merge preferred
   - Delete branch after merge

## Adding New Features

### Adding a Block Type

See [README_SETUP.md](README_SETUP.md#adding-a-new-block-type) for detailed guide.

### Adding a Language

See [README.md](README.md#adding-languages) for language addition guide.

### Adding a Theme

1. Edit `lib/export.php`:

```php
$colors = [
    'minimal' => ['bg' => '#ffffff', 'text' => '#1a1a1a', 'accent' => '#0066cc'],
    'new_theme' => ['bg' => '#f0f0f0', 'text' => '#333333', 'accent' => '#ff6600'],
];
```

2. Add translation key in all `i18n/ui.*.json`:

```json
{
  "theme_new_theme": "New Theme"
}
```

3. Update select options in `public/assets/js/app.js`.

## Documentation

### Code Comments

```php
/**
 * Export project to ZIP file
 * 
 * @param int $projectId The project ID to export
 * @return string Path to generated ZIP file
 * @throws Exception If project not found or export fails
 */
function exportProject(int $projectId): string {
    // Implementation
}
```

### README Updates

- Keep README.md concise and user-friendly
- Technical details go in README_SETUP.md
- Quick instructions go in QUICKSTART.md

## Questions?

- Check existing documentation
- Review similar implementations in codebase
- Open an issue for discussion
- Contact maintainers

## License

By contributing, you agree that your contributions will be licensed under the same license as the project (see LICENSE file).

---

Thank you for contributing to Portfolio Builder! 🎨
