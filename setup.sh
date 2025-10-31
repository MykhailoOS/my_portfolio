#!/bin/bash

echo "========================================="
echo "Portfolio Builder - Setup Script"
echo "========================================="
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo "⚠️  Please do not run as root"
    exit 1
fi

# Check PHP version
echo "Checking PHP version..."
PHP_VERSION=$(php -r 'echo PHP_VERSION;' 2>/dev/null)
if [ -z "$PHP_VERSION" ]; then
    echo "❌ PHP is not installed"
    exit 1
fi

PHP_MAJOR=$(php -r 'echo PHP_MAJOR_VERSION;')
PHP_MINOR=$(php -r 'echo PHP_MINOR_VERSION;')

if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]); then
    echo "❌ PHP 8.2 or higher is required (found $PHP_VERSION)"
    exit 1
fi
echo "✅ PHP $PHP_VERSION"

# Check required extensions
echo ""
echo "Checking PHP extensions..."
REQUIRED_EXTS=("pdo" "pdo_mysql" "zip" "gd" "json")
for ext in "${REQUIRED_EXTS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        echo "✅ $ext"
    else
        echo "❌ $ext (missing)"
        MISSING_EXTS="$MISSING_EXTS $ext"
    fi
done

if [ -n "$MISSING_EXTS" ]; then
    echo ""
    echo "❌ Missing extensions:$MISSING_EXTS"
    echo "Install them and try again"
    exit 1
fi

# Check MySQL
echo ""
echo "Checking MySQL..."
if command -v mysql &> /dev/null; then
    MYSQL_VERSION=$(mysql --version | grep -oP '\d+\.\d+\.\d+' | head -1)
    echo "✅ MySQL $MYSQL_VERSION"
else
    echo "⚠️  MySQL not found (you can set it up separately)"
fi

# Create directories
echo ""
echo "Creating directories..."
mkdir -p public/uploads
mkdir -p public/assets/img
mkdir -p public/assets/css
mkdir -p public/assets/js/vendor
echo "✅ Directories created"

# Set permissions
echo ""
echo "Setting permissions..."
chmod 755 public/uploads
chmod 755 public/assets/img
echo "✅ Permissions set"

# Copy environment file
if [ ! -f .env ]; then
    echo ""
    echo "Creating .env file..."
    cp .env.example .env
    echo "✅ .env created (please edit with your database credentials)"
else
    echo "⚠️  .env already exists"
fi

# Database setup prompt
echo ""
echo "========================================="
echo "Database Setup"
echo "========================================="
echo ""
echo "Do you want to import the database schema now? (y/n)"
read -r IMPORT_DB

if [ "$IMPORT_DB" = "y" ]; then
    echo ""
    echo "Enter MySQL root password:"
    read -rs MYSQL_ROOT_PASS
    
    echo ""
    echo "Creating database..."
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS portfolio_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✅ Database created"
        
        echo "Importing schema..."
        mysql -u root -p"$MYSQL_ROOT_PASS" portfolio_builder < sql/schema.sql 2>/dev/null
        
        if [ $? -eq 0 ]; then
            echo "✅ Schema imported"
        else
            echo "❌ Failed to import schema"
            echo "You can import manually: mysql -u root -p portfolio_builder < sql/schema.sql"
        fi
    else
        echo "❌ Failed to create database"
        echo "You can create manually: mysql -u root -p < sql/schema.sql"
    fi
else
    echo "⏭️  Skipping database import"
    echo "Run manually: mysql -u root -p < sql/schema.sql"
fi

# Final instructions
echo ""
echo "========================================="
echo "Setup Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Edit .env with your database credentials"
echo "2. Configure your web server (Apache/Nginx)"
echo "3. Point document root to: public/"
echo "4. Enable mod_rewrite (Apache) or configure rewrite rules (Nginx)"
echo "5. Access the application in your browser"
echo ""
echo "For detailed instructions, see README_SETUP.md"
echo ""
echo "Happy building! 🎨"
