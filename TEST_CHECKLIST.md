# Testing Checklist for CORS Fix

## Pre-Deployment Verification

Before deploying to your hosting provider, ensure all files are present:

```bash
# Check that all modified files exist
ls -l public/.htaccess
ls -l public/api.php
ls -l public/index.php
ls -l lib/utils.php
ls -l public/assets/js/app.js

# Check that new test files exist
ls -l public/test-cors.php
ls -l public/test-cors.html
ls -l CORS_FIX.md
```

## Post-Deployment Testing

### Step 1: CORS Headers Verification

1. **Open the CORS test page**
   - URL: `https://yourdomain.com/test-cors.html`
   - Expected: Page loads without errors

2. **Run automated tests**
   - Click "Run All Tests" button
   - Expected: All three tests show "PASS" status
   - If any test fails, see CORS_FIX.md for troubleshooting

3. **Check test results**
   - Test 1: API CORS Headers ✓
   - Test 2: CORS Test Endpoint ✓
   - Test 3: Preflight Request ✓

### Step 2: API Endpoint Testing

1. **Direct API test**
   - URL: `https://yourdomain.com/test-cors.php`
   - Expected: JSON response with status "success"
   - Verify CORS headers are listed in the response

2. **Check response headers**
   - Open browser DevTools (F12)
   - Go to Network tab
   - Visit: `https://yourdomain.com/test-cors.php`
   - Look for these headers in the Response Headers:
     - ✓ `Access-Control-Allow-Origin: *`
     - ✓ `Access-Control-Allow-Methods: GET, POST, OPTIONS`
     - ✓ `Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization`

### Step 3: Application Functionality

1. **Open the main application**
   - URL: `https://yourdomain.com/`
   - Expected: Application loads without CORS errors
   - Check browser console (F12) for any errors

2. **Create a new project**
   - Click "Create Project" or "New" button
   - Enter project name (e.g., "Test Project")
   - Select at least one language (EN is default)
   - Choose a theme
   - Click "Create Project"
   - Expected: ✓ Project created successfully
   - Expected: ✓ No CORS errors in console
   - Expected: ✓ Blocks appear in the sidebar

3. **Edit blocks**
   - Click on a block in the sidebar (e.g., "Hero")
   - Modify content (e.g., change title)
   - Wait 1 second for auto-save
   - Expected: ✓ Changes saved automatically
   - Expected: ✓ Canvas updates with new content
   - Expected: ✓ No errors in console

4. **Test language switching**
   - Click on language tabs (EN, UK, RU, PL)
   - Expected: ✓ Language switches successfully
   - Expected: ✓ Inspector shows language-specific content
   - Expected: ✓ No errors in console

5. **Test block reordering**
   - Drag a block by the ☰ handle
   - Move it to a different position
   - Release
   - Expected: ✓ Block reordered successfully
   - Expected: ✓ Canvas updates to show new order
   - Expected: ✓ No errors in console

6. **Test export**
   - Click "Export ZIP" button
   - Expected: ✓ Download starts
   - Expected: ✓ ZIP file downloads successfully
   - Expected: ✓ No errors in console

### Step 4: Error Handling

1. **Test with invalid project ID**
   - Open browser console (F12)
   - Run: `localStorage.setItem('currentProjectId', 999999)`
   - Refresh page
   - Expected: ✓ Helpful error message appears
   - Expected: ✓ Error logged to console
   - Expected: ✓ Create project modal appears after error

2. **Check console logs**
   - All errors should include detailed information
   - No "CORS policy" errors should appear
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

2. **Test touch interactions**
   - Tap blocks to edit
   - Use drawers and sheets
   - Test all functionality from desktop checklist

3. **Test mobile CORS test page**
   - Visit `https://yourdomain.com/test-cors.html`
   - Run tests
   - Expected: All tests pass on mobile

## Console Error Check

Open browser console (F12 → Console) and verify:

### ✓ No CORS errors
```
✗ Cross-Origin Request Blocked... (SHOULD NOT APPEAR)
✗ CORS header 'Access-Control-Allow-Origin' missing (SHOULD NOT APPEAR)
```

### ✓ Only expected logs
```
✓ Info logs about actions (normal)
✓ Debug logs with detailed information (normal)
✓ Network activity logs (normal)
```

## Network Tab Check

Open browser DevTools (F12 → Network):

1. **Filter by XHR**
   - Click "XHR" filter
   - Perform actions (create project, edit block, etc.)
   - Check each API request

2. **Verify response headers**
   - Click on any `api.php` request
   - Go to "Headers" tab
   - Scroll to "Response Headers"
   - Verify CORS headers are present:
     ```
     Access-Control-Allow-Origin: *
     Access-Control-Allow-Methods: GET, POST, OPTIONS
     Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization
     Content-Type: application/json
     ```

3. **Check status codes**
   - All successful requests: 200
   - No 404 errors to infinityfree.net
   - No unexpected redirects

## Common Issues and Solutions

### Issue: "mod_headers not available"
**Solution**: Contact your hosting provider to enable Apache mod_headers module

### Issue: Tests pass but application doesn't work
**Solutions**:
1. Clear browser cache and cookies
2. Check PHP error logs on server
3. Verify database connection in .env file
4. Check file permissions (api.php should be readable)

### Issue: 404 errors still redirect to InfinityFree error page
**Solutions**:
1. Verify .htaccess file is uploaded to public/ directory
2. Check that .htaccess is not being ignored (needs AllowOverride All)
3. Try uploading .htaccess via FTP in binary mode

### Issue: CORS headers not appearing
**Solutions**:
1. Check if mod_headers is enabled: Contact hosting support
2. Verify .htaccess syntax (no typos)
3. Check if hosting provider allows Header directives
4. Try alternative CORS configuration in CORS_FIX.md

## Success Criteria

Mark each as complete:

- [ ] CORS test page loads and all tests pass
- [ ] No CORS errors in browser console
- [ ] Project creation works without errors
- [ ] Block editing and auto-save work
- [ ] Language switching works
- [ ] Block reordering works
- [ ] ZIP export downloads successfully
- [ ] All response headers include CORS headers
- [ ] No redirects to infinityfree.net error pages
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

If tests fail after applying this fix:

1. Document which specific tests failed
2. Include browser console errors (full text)
3. Include network tab screenshots showing headers
4. Note your hosting provider name
5. Check if mod_headers is available
6. Review server error logs

Submit issues with all this information for faster resolution.

---

**Version**: 1.1.0  
**Last Updated**: 2024  
**Status**: Active

✅ **All tests passing = Fix successful!**
