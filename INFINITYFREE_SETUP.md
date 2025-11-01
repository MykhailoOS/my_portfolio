# InfinityFree Hosting Setup Guide

## Database Configuration

Your database is already configured in `.env`:

```
DB_HOST=sql308.infinityfree.com
DB_NAME=if0_39948852_portfolio_maker
DB_USER=if0_39948852
DB_PASS=MF10WtR86K8GIHA
DB_PORT=3306
```

## Step-by-Step Deployment

### 1. Create Database Tables

1. Login to InfinityFree Control Panel (cPanel)
2. Go to **MySQL Databases** → **phpMyAdmin**
3. Select database `if0_39948852_portfolio_maker`
4. Click **SQL** tab
5. Copy and paste contents of `sql/schema.sql`
6. Click **Go** to execute

### 2. Upload Files

**Option A: FTP Upload (Recommended)**

1. Connect via FTP to InfinityFree:
   - Host: `ftpupload.net`
   - Username: Your InfinityFree username
   - Password: Your InfinityFree password
   
2. Navigate to `htdocs/` directory

3. Upload ALL files from project root:
   ```
   ✓ index.php
   ✓ api.php
   ✓ .htaccess
   ✓ .env
   ✓ assets/ (folder)
   ✓ lib/ (folder)
   ✓ i18n/ (folder)
   ✓ uploads/ (folder - create if empty)
   ```

**Option B: File Manager Upload**

1. Login to InfinityFree Control Panel
2. Open **File Manager**
3. Navigate to `htdocs/`
4. Upload files one by one or as ZIP archive

### 3. Set Permissions

In File Manager or via FTP:

```
uploads/         → 755 (rwxr-xr-x)
.htaccess        → 644 (rw-r--r--)
index.php        → 644 (rw-r--r--)
api.php          → 644 (rw-r--r--)
```

### 4. Test Your Site

1. Visit: `http://yoursite.infinityfree.com/`
2. You should see the Portfolio Builder interface
3. Click "Create Project" to test database connection

## Common InfinityFree Issues

### Issue: 404 Error on Assets

**Cause**: `.htaccess` not uploaded or not working

**Solution**:
1. Ensure `.htaccess` is uploaded
2. Check file permissions (644)
3. Re-upload in **ASCII mode** (not binary)

### Issue: Database Connection Failed

**Cause**: Wrong credentials or database not created

**Solution**:
1. Double-check database name in `.env`
2. Verify database exists in phpMyAdmin
3. Run `sql/schema.sql` to create tables

### Issue: Can't Upload Images

**Cause**: `uploads/` folder doesn't exist or no permissions

**Solution**:
1. Create `uploads/` folder if missing
2. Set permissions to 755
3. Check PHP upload limits (InfinityFree default: 10MB)

### Issue: 403 Forbidden Error

**Cause**: Incorrect file permissions

**Solution**:
1. Set `.htaccess` to 644
2. Set `index.php` to 644
3. Set folders to 755

### Issue: Blank White Page

**Cause**: PHP error, check error logs

**Solution**:
1. In cPanel → Error Logs
2. Look for PHP errors
3. Common: Missing PHP extensions or syntax errors

## InfinityFree Limitations

- **PHP Version**: 8.1+ (check your account)
- **Upload Limit**: 10MB per file
- **Database Size**: Limited on free plan
- **Execution Time**: 60 seconds
- **Memory Limit**: 256MB
- **No HTTPS on Free Subdomain**: Use custom domain for SSL

## Verification Checklist

After deployment, test:

- [ ] Homepage loads: `http://yoursite.infinityfree.com/`
- [ ] Create project works (tests database)
- [ ] Edit blocks and save (tests AJAX)
- [ ] Upload image (tests file upload)
- [ ] Export ZIP (tests export function)
- [ ] No errors in browser console (F12)

## Support

If you encounter issues:

1. Check InfinityFree error logs in cPanel
2. Check browser console (F12) for JavaScript errors
3. Verify all files are uploaded
4. Double-check database credentials
5. See [TEST_CHECKLIST.md](TEST_CHECKLIST.md) for detailed testing

## Upgrading to Paid Hosting

For production use, consider upgrading to paid hosting for:
- Better performance
- No ads
- SSL certificate included
- Better support
- More resources

Recommended hosts: Namecheap, SiteGround, or DigitalOcean.

---

**Your Site URL**: `http://yoursite.infinityfree.com/`  
**Database**: `if0_39948852_portfolio_maker`  
**FTP Host**: `ftpupload.net`

Good luck! 🚀
