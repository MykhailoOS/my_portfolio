# Portfolio Builder - Deployment Guide

This guide covers deploying Portfolio Builder to production environments.

## 📋 Pre-Deployment Checklist

### Server Requirements
- [ ] PHP 8.2+ installed
- [ ] MySQL 8.x running
- [ ] Apache/Nginx configured
- [ ] SSL certificate (recommended)
- [ ] Domain name configured

### PHP Extensions
- [ ] PDO
- [ ] PDO_MySQL
- [ ] ZIP
- [ ] GD or Imagick
- [ ] JSON
- [ ] Session

### Security
- [ ] Firewall configured (block direct MySQL access)
- [ ] Strong database passwords
- [ ] PHP security hardening
- [ ] File permissions set correctly
- [ ] HTTPS enabled

## 🚀 Deployment Steps

### 1. Prepare Files

```bash
# Clone repository
git clone <repository-url> portfolio-builder
cd portfolio-builder

# Remove unnecessary files
rm -rf .git
rm PROJECT_SUMMARY.md CONTRIBUTING.md

# Set permissions
chmod 755 public/uploads
chmod 755 setup.sh
```

### 2. Database Setup

```bash
# Create database
mysql -u root -p
```

```sql
CREATE DATABASE portfolio_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON portfolio_builder.* TO 'portfolio_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Import schema
mysql -u portfolio_user -p portfolio_builder < sql/schema.sql
```

### 3. Environment Configuration

```bash
# Create .env file
cp .env.example .env
nano .env
```

Set production values:
```env
DB_HOST=localhost
DB_NAME=portfolio_builder
DB_USER=portfolio_user
DB_PASS=strong_password_here

APP_ENV=production
APP_DEBUG=false
```

### 4. Web Server Configuration

#### Apache

**VirtualHost (recommended)**

```apache
<VirtualHost *:80>
    ServerName portfolio.example.com
    ServerAlias www.portfolio.example.com
    
    DocumentRoot /var/www/portfolio-builder/public
    
    <Directory /var/www/portfolio-builder/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/portfolio_error.log
    CustomLog ${APACHE_LOG_DIR}/portfolio_access.log combined
    
    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName portfolio.example.com
    ServerAlias www.portfolio.example.com
    
    DocumentRoot /var/www/portfolio-builder/public
    
    <Directory /var/www/portfolio-builder/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/portfolio.example.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/portfolio.example.com/privkey.pem
    
    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/portfolio_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/portfolio_ssl_access.log combined
</VirtualHost>
```

Enable and restart:
```bash
sudo a2ensite portfolio.conf
sudo a2enmod ssl rewrite headers
sudo systemctl restart apache2
```

#### Nginx

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name portfolio.example.com www.portfolio.example.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name portfolio.example.com www.portfolio.example.com;
    
    root /var/www/portfolio-builder/public;
    index index.php;
    
    # SSL
    ssl_certificate /etc/letsencrypt/live/portfolio.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/portfolio.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Logs
    access_log /var/log/nginx/portfolio_access.log;
    error_log /var/log/nginx/portfolio_error.log;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
    
    # Deny access to sensitive files
    location ~ /(\.env|\.git|composer\.(json|lock)|package\.json) {
        deny all;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Test and reload:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 5. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt update
sudo apt install certbot python3-certbot-apache
# OR for Nginx
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --apache -d portfolio.example.com -d www.portfolio.example.com
# OR for Nginx
sudo certbot --nginx -d portfolio.example.com -d www.portfolio.example.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### 6. File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/portfolio-builder

# Set permissions
sudo find /var/www/portfolio-builder -type d -exec chmod 755 {} \;
sudo find /var/www/portfolio-builder -type f -exec chmod 644 {} \;

# Writable directories
sudo chmod 775 /var/www/portfolio-builder/public/uploads
sudo chmod 775 /var/www/portfolio-builder/public/assets/img
```

### 7. PHP Configuration (php.ini)

```ini
# Find php.ini location
php --ini

# Edit (usually /etc/php/8.2/apache2/php.ini or /etc/php/8.2/fpm/php.ini)
upload_max_filesize = 5M
post_max_size = 50M
max_execution_time = 60
memory_limit = 256M
session.cookie_httponly = On
session.cookie_secure = On
session.use_strict_mode = On
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

Restart after changes:
```bash
sudo systemctl restart apache2
# OR
sudo systemctl restart php8.2-fpm
```

## 🔒 Security Hardening

### 1. File System

```bash
# Disable directory listing
# Already in .htaccess: Options -Indexes

# Protect sensitive files
sudo chmod 600 .env
sudo chmod 600 sql/schema.sql

# Remove write permissions from code
sudo find lib/ public/*.php -type f -exec chmod 444 {} \;
```

### 2. Database

```sql
-- Use strong passwords
-- Limit privileges
REVOKE ALL PRIVILEGES ON portfolio_builder.* FROM 'portfolio_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON portfolio_builder.* TO 'portfolio_user'@'localhost';
FLUSH PRIVILEGES;

-- Regular backups
mysqldump -u root -p portfolio_builder > backup_$(date +%Y%m%d).sql
```

### 3. Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Block MySQL from outside
sudo ufw deny 3306/tcp
```

### 4. Fail2Ban (Optional)

```bash
sudo apt install fail2ban

# Configure for Apache/Nginx
sudo nano /etc/fail2ban/jail.local
```

```ini
[apache-auth]
enabled = true

[apache-badbots]
enabled = true

[apache-noscript]
enabled = true

[apache-overflows]
enabled = true
```

```bash
sudo systemctl restart fail2ban
```

## 📊 Monitoring

### 1. Log Monitoring

```bash
# Apache logs
tail -f /var/log/apache2/portfolio_error.log

# Nginx logs
tail -f /var/log/nginx/portfolio_error.log

# PHP logs
tail -f /var/log/php_errors.log

# MySQL logs
sudo tail -f /var/log/mysql/error.log
```

### 2. Disk Space

```bash
# Check uploads directory
du -sh /var/www/portfolio-builder/public/uploads

# Set up alerts (cron + script)
```

### 3. Database Size

```sql
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'portfolio_builder'
ORDER BY (data_length + index_length) DESC;
```

## 🔄 Backup Strategy

### Automated Daily Backups

```bash
# Create backup script
sudo nano /usr/local/bin/portfolio-backup.sh
```

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/var/backups/portfolio"
PROJECT_DIR="/var/www/portfolio-builder"
DB_NAME="portfolio_builder"
DB_USER="portfolio_user"
DB_PASS="your_password"
RETENTION_DAYS=7

# Create backup directory
mkdir -p $BACKUP_DIR

# Timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$TIMESTAMP.tar.gz -C $PROJECT_DIR/public uploads

# Delete old backups
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "uploads_*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completed: $TIMESTAMP"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/portfolio-backup.sh

# Add to cron (daily at 2 AM)
sudo crontab -e
```

Add line:
```
0 2 * * * /usr/local/bin/portfolio-backup.sh >> /var/log/portfolio-backup.log 2>&1
```

## 🚦 Performance Optimization

### 1. PHP OPcache

Edit `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. MySQL Tuning

```sql
-- Check current settings
SHOW VARIABLES LIKE 'innodb_buffer_pool_size';

-- Optimize (adjust for available RAM)
SET GLOBAL innodb_buffer_pool_size = 256M;
SET GLOBAL innodb_log_file_size = 128M;
```

### 3. Gzip Compression

Apache (already in .htaccess):
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

Nginx:
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
gzip_comp_level 6;
```

## 🧪 Post-Deployment Testing

### Functional Tests

- [ ] Access homepage (https://portfolio.example.com)
- [ ] Create a test project
- [ ] Add/edit blocks
- [ ] Upload an image
- [ ] Change language
- [ ] Export ZIP
- [ ] Verify exported site works

### Security Tests

- [ ] HTTPS working
- [ ] HTTP redirects to HTTPS
- [ ] Headers present (use securityheaders.com)
- [ ] File upload restrictions work
- [ ] SQL injection attempts fail
- [ ] XSS attempts sanitized

### Performance Tests

- [ ] Page load < 2s (use GTmetrix or PageSpeed Insights)
- [ ] Mobile score > 90
- [ ] No console errors
- [ ] Images optimized

## 🐛 Troubleshooting

### Common Issues

**1. 500 Internal Server Error**
```bash
# Check logs
tail -f /var/log/apache2/error.log
# OR
tail -f /var/log/nginx/error.log

# Check permissions
ls -la /var/www/portfolio-builder/public
```

**2. Database Connection Failed**
```bash
# Test connection
mysql -u portfolio_user -p portfolio_builder

# Check .env file
cat /var/www/portfolio-builder/.env

# Verify credentials
```

**3. Upload Fails**
```bash
# Check permissions
ls -la /var/www/portfolio-builder/public/uploads

# Check PHP limits
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

**4. Export Fails**
```bash
# Check ZIP extension
php -m | grep zip

# Check temp directory
ls -la /tmp

# Check permissions
```

## 📞 Support

### Resources
- Documentation: README_SETUP.md
- Logs: /var/log/apache2/ or /var/log/nginx/
- PHP errors: /var/log/php_errors.log
- MySQL errors: /var/log/mysql/error.log

### Emergency Contacts
- Hosting provider support
- Database administrator
- System administrator

---

## 📝 Deployment Checklist Summary

- [ ] Server requirements met
- [ ] Database created and configured
- [ ] Files uploaded and permissions set
- [ ] Web server configured
- [ ] SSL certificate installed
- [ ] .env file configured for production
- [ ] Security hardening applied
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] Functional tests passed
- [ ] Security tests passed
- [ ] Performance optimized
- [ ] Documentation updated

---

**Deployment Time**: ~1 hour (excluding SSL certificate wait time)
**Difficulty**: Intermediate
**Support**: See README_SETUP.md for detailed troubleshooting
