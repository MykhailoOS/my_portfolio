<?php
require_once __DIR__ . '/../lib/utils.php';

$csrfToken = generateCsrf();
$editorLang = $_COOKIE['editorLang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($editorLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <title>Portfolio Builder</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="app-header-left">
                <button class="btn btn-icon menu-toggle" aria-label="Toggle menu">
                    <span>☰</span>
                </button>
                <span class="app-title">Portfolio Builder</span>
            </div>
            
            <div class="app-header-center">
                <div class="project-name">—</div>
            </div>
            
            <div class="app-header-right">
                <button class="btn btn-sm inspector-toggle" style="display: none;">
                    <span>Inspector</span>
                </button>
                <button class="btn btn-sm" id="preview-btn">
                    <span>Preview</span>
                </button>
                <button class="btn btn-primary btn-sm" id="export-zip-btn">
                    <span>Export ZIP</span>
                </button>
            </div>
        </header>

        <div class="app-main">
            <aside class="sidebar">
                <div class="sidebar-header">
                    <span>Blocks</span>
                    <button class="btn btn-sm" id="create-project-btn">New</button>
                </div>
                <div class="blocks-list">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </aside>

            <main class="canvas">
                <div class="canvas-inner">
                    <div class="empty-state">
                        <p>Create a project to start building</p>
                    </div>
                </div>
            </main>

            <aside class="inspector">
                <div class="inspector-tabs">
                    <button class="inspector-tab active" data-tab="content">Content</button>
                    <button class="inspector-tab" data-tab="theme">Theme</button>
                    <button class="inspector-tab" data-tab="seo">SEO</button>
                </div>
                
                <div class="language-tabs"></div>
                
                <div class="inspector-content">
                    <div class="empty-state">
                        <p>Select a block to edit</p>
                    </div>
                </div>
            </aside>

            <div class="drawer-overlay"></div>
        </div>
    </div>

    <button class="fab" aria-label="Add block">+</button>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>
    <script src="/assets/js/vendor/sortable.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
