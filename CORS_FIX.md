# CORS Fix for InfinityFree and Similar Hosting Providers

## Problem

When deploying the Portfolio Builder on certain hosting providers (like InfinityFree), you may encounter CORS (Cross-Origin Resource Sharing) errors such as:

```
Cross-Origin Request Blocked: The Same Origin Policy disallows reading the remote resource at https://errors.infinityfree.net/errors/404/. (Reason: CORS header 'Access-Control-Allow-Origin' missing). Status code: 200.
```

This error occurs when:
1. The API endpoints return responses without proper CORS headers
2. The hosting provider's error pages (404, 500, etc.) don't include CORS headers
3. AJAX requests from the frontend are blocked by the browser's same-origin policy

## Solution

This project now includes comprehensive CORS support to prevent these issues:

### 1. Apache .htaccess Configuration

The `public/.htaccess` file includes CORS headers:

```apache
# CORS headers
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, X-CSRF-Token, Authorization"
Header set Access-Control-Max-Age "3600"
```

### 2. PHP API CORS Headers

The `public/api.php` file sets CORS headers before any other output:

```php
// Set CORS headers first to ensure they're always sent
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
header('Access-Control-Max-Age: 3600');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

### 3. Response Functions

The `lib/utils.php` file includes CORS headers in all JSON responses:

```php
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    
    // Ensure CORS headers are set for all responses
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
    
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

### 4. JavaScript Error Handling

The `public/assets/js/app.js` file includes better error handling for AJAX requests:

```javascript
$.ajax({
    url: '/api.php',
    type: 'POST',
    data: formData,
    crossDomain: false,
    success: (response) => {
        // Handle success
    },
    error: (xhr, status, error) => {
        console.error('Error:', status, error, xhr);
        // Display helpful error message
    }
});
```

## For Hosting Providers

### InfinityFree Specific Issues

If you're using InfinityFree or similar free hosting providers:

1. **Check if mod_headers is enabled**: The CORS headers in `.htaccess` require Apache's `mod_headers` module
2. **Verify .htaccess is being processed**: Ensure `AllowOverride All` is set in your Apache configuration
3. **Check PHP version**: Ensure you're using PHP 8.2 or higher
4. **Review error logs**: Check both Apache and PHP error logs for any issues

### Alternative: Nginx Configuration

If using Nginx instead of Apache, add this to your server block:

```nginx
location / {
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, X-CSRF-Token, Authorization' always;
    add_header 'Access-Control-Max-Age' '3600' always;
    
    if ($request_method = 'OPTIONS') {
        return 200;
    }
    
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, X-CSRF-Token, Authorization' always;
    
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

## Security Considerations

The current implementation uses `Access-Control-Allow-Origin: *` which allows requests from any domain. For production use, you should:

1. **Restrict to specific domains**:
   ```apache
   Header set Access-Control-Allow-Origin "https://yourdomain.com"
   ```

2. **Use environment-based configuration**:
   ```php
   $allowedOrigin = $_ENV['CORS_ORIGIN'] ?? '*';
   header("Access-Control-Allow-Origin: $allowedOrigin");
   ```

3. **Implement authentication**: Add proper user authentication before deploying to production

## Troubleshooting

### Still getting CORS errors?

1. **Clear browser cache**: CORS preflight responses are cached
2. **Check browser console**: Look for detailed error messages
3. **Verify API responses**: Use browser DevTools Network tab to inspect headers
4. **Test with curl**:
   ```bash
   curl -I https://yourdomain.com/api.php?action=project.get&id=1
   ```
   Look for `Access-Control-Allow-Origin` in the response headers

### API returning 404 errors?

1. **Check .htaccess is uploaded**: Ensure `.htaccess` file is in the `public/` directory
2. **Verify rewrite rules work**: Test if `/api.php?action=project.get` works directly
3. **Check file permissions**: Ensure `api.php` has read permissions (644)

### InfinityFree specific 404 redirect?

If InfinityFree is redirecting all 404s to their error page:

1. The custom error document in `.htaccess` should help
2. Ensure all API requests use the full path: `/api.php`
3. Consider using absolute URLs in AJAX calls if needed

## Testing

After implementing these fixes, test the following:

1. **Create a new project**: Should work without CORS errors
2. **Load existing project**: Should load successfully
3. **Update blocks**: Auto-save should work
4. **Upload media**: File uploads should complete
5. **Export ZIP**: Export should download successfully

Check the browser console for any remaining errors.

## Support

If you continue to experience CORS issues after applying these fixes:

1. Check that all files are uploaded correctly
2. Verify your hosting provider supports `.htaccess` directives
3. Review the browser console for specific error messages
4. Check server error logs for PHP errors

## Updates

This fix was added to resolve CORS issues specifically on InfinityFree hosting but applies to any hosting provider that may have similar CORS restrictions.

**Version**: 1.1.0  
**Date**: 2024  
**Status**: Active
