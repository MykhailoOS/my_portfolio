# Testing Checklist for Portfolio Builder

## Pre-Deployment Verification

Before deploying to your hosting provider, ensure all files are present:

```bash
# Check that core files exist
ls -l public/.htaccess
ls -l public/api.php
ls -l public/index.php
ls -l lib/utils.php
ls -l public/assets/js/app.js
ls -l sql/schema.sql
```

## Post-Deployment Testing

### Step 1: Application Loads

1. **Open the main application**
   - URL: `https://yourdomain.com/`
   - Expected: Application loads without errors
   - Check browser console (F12) for any errors

### Step 2: Project Management

1. **Create a new project**
   - Click "Create Project" or "New" button
   - Enter project name (e.g., "Test Project")
   - Select at least one language (EN is default)
   - Choose a theme
   - Click "Create Project"
   - Expected: ✓ Project created successfully
   - Expected: ✓ No errors in console
   - Expected: ✓ Blocks appear in the sidebar

2. **Edit blocks**
   - Click on a block in the sidebar (e.g., "Hero")
   - Modify content (e.g., change title)
   - Wait 1 second for auto-save
   - Expected: ✓ Changes saved automatically
   - Expected: ✓ Canvas updates with new content
   - Expected: ✓ No errors in console

3. **Test language switching**
   - Click on language tabs (EN, UK, RU, PL)
   - Expected: ✓ Language switches successfully
   - Expected: ✓ Inspector shows language-specific content
   - Expected: ✓ No errors in console

4. **Test block reordering**
   - Drag a block by the ☰ handle
   - Move it to a different position
   - Release
   - Expected: ✓ Block reordered successfully
   - Expected: ✓ Canvas updates to show new order
   - Expected: ✓ No errors in console

5. **Test media upload**
   - Edit a block that supports images (Hero, Projects)
   - Click upload button
   - Select an image (JPEG, PNG, WebP, or SVG)
   - Expected: ✓ Image uploads successfully
   - Expected: ✓ Preview appears in canvas
   - Expected: ✓ No errors in console

6. **Test export**
   - Click "Export ZIP" button
   - Expected: ✓ Download starts
   - Expected: ✓ ZIP file downloads successfully
   - Expected: ✓ No errors in console

### Step 3: Error Handling

1. **Test with invalid project ID**
   - Open browser console (F12)
   - Run: `localStorage.setItem('currentProjectId', 999999)`
   - Refresh page
   - Expected: ✓ Helpful error message appears
   - Expected: ✓ Create project modal appears after error

2. **Check console logs**
   - All errors should include detailed information
   - Network errors should have descriptive messages

## Browser Testing

Test on multiple browsers to ensure compatibility:

- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari (if available)
- [ ] Edge (if available)
- [ ] Mobile Safari (iOS)
- [ ] Mobile Chrome (Android)

## Mobile Testing

1. **Open on mobile device**
   - Visit `https://yourdomain.com/`
   - Expected: Responsive layout
   - Expected: Touch-friendly interface

2. **Test touch interactions**
   - Tap blocks to edit
   - Use drawers and sheets
   - Long-press to drag blocks
   - Test all functionality from desktop checklist

3. **Test mobile-specific features**
   - FAB (Floating Action Button) appears
   - Drawers slide in/out smoothly
   - Keyboard doesn't break layout
   - Safe area insets work on notched devices

## Network Tab Check

Open browser DevTools (F12 → Network):

1. **Filter by XHR**
   - Click "XHR" filter
   - Perform actions (create project, edit block, etc.)
   - Check each API request

2. **Check status codes**
   - All successful requests: 200
   - No unexpected 404 errors
   - No unexpected redirects

3. **Verify responses**
   - Click on any `api.php` request
   - Go to "Response" tab
   - Verify JSON is valid

## Common Issues and Solutions

### Issue: Database connection error
**Solution**: Check `.env` file credentials and verify MySQL service is running

### Issue: Permission denied on file upload
**Solution**: Ensure web server has write access to `public/uploads/` directory
```bash
chmod 755 public/uploads
chown www-data:www-data public/uploads  # or appropriate user
```

### Issue: 404 errors on API endpoints
**Solutions**:
1. Verify .htaccess file is uploaded to `public/` directory
2. Check that mod_rewrite is enabled
3. Verify AllowOverride is set to All in Apache config

### Issue: ZIP export fails
**Solutions**:
1. Check PHP ZIP extension is installed: `php -m | grep zip`
2. Increase PHP memory_limit: `memory_limit = 256M`
3. Increase max_execution_time: `max_execution_time = 60`

### Issue: Images not uploading
**Solutions**:
1. Check PHP upload limits in php.ini or .htaccess
2. Verify GD or Imagick extension is installed
3. Check file permissions on uploads directory

## Success Criteria

Mark each as complete:

- [ ] Application loads without errors
- [ ] No errors in browser console
- [ ] Project creation works
- [ ] Block editing and auto-save work
- [ ] Language switching works
- [ ] Block reordering works
- [ ] Media upload works
- [ ] ZIP export downloads successfully
- [ ] Mobile version works properly
- [ ] Tested on multiple browsers

## Final Verification

After all tests pass:

1. **Delete test project** (if created for testing)
2. **Create a real project** with actual content
3. **Use the application normally** for a few minutes
4. **Export and verify** the exported ZIP file
5. **Check logs** one final time for any issues

## Reporting Issues

If tests fail:

1. Document which specific tests failed
2. Include browser console errors (full text)
3. Include network tab information
4. Note your hosting provider and PHP version
5. Check server error logs

---

**Version**: 1.1.0  
**Last Updated**: 2024

✅ **All tests passing = Deployment successful!**
