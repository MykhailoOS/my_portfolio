<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

class PortfolioExporter {
    private $projectId;
    private $project;
    private $pages;
    private $blocks;
    private $media;
    private $exportPath;
    
    public function __construct($projectId) {
        $this->projectId = $projectId;
        $this->loadProjectData();
    }
    
    private function loadProjectData() {
        $this->project = fetchOne(
            "SELECT * FROM projects WHERE id = ?",
            [$this->projectId]
        );
        
        if (!$this->project) {
            throw new Exception('Project not found');
        }
        
        $this->project['languages'] = json_decode($this->project['languages'], true);
        $this->project['theme'] = json_decode($this->project['theme'], true);
        
        $this->pages = fetchAll(
            "SELECT * FROM pages WHERE project_id = ? ORDER BY `order`",
            [$this->projectId]
        );
        
        $this->blocks = fetchAll(
            "SELECT * FROM blocks WHERE project_id = ? ORDER BY page_id, `order`",
            [$this->projectId]
        );
        
        $this->media = fetchAll(
            "SELECT * FROM media WHERE project_id = ?",
            [$this->projectId]
        );
    }
    
    public function export() {
        $tmpDir = sys_get_temp_dir() . '/portfolio_export_' . uniqid();
        ensureDir($tmpDir);
        
        $this->exportPath = $tmpDir;
        
        $this->createStructure();
        $this->copyAssets();
        $this->copyMedia();
        $this->generateLanguagePages();
        $this->generateContactHandler();
        $this->generateReadme();
        
        $zipPath = $tmpDir . '.zip';
        $this->createZip($tmpDir, $zipPath);
        
        $this->logExport(filesize($zipPath));
        
        return $zipPath;
    }
    
    private function createStructure() {
        $dirs = ['assets/css', 'assets/js', 'assets/img'];
        
        foreach ($this->project['languages'] as $lang) {
            $dirs[] = $lang;
        }
        
        foreach ($dirs as $dir) {
            ensureDir($this->exportPath . '/' . $dir);
        }
    }
    
    private function copyAssets() {
        $css = $this->generateCSS();
        file_put_contents($this->exportPath . '/assets/css/style.css', $css);
        
        $js = $this->generateJS();
        file_put_contents($this->exportPath . '/assets/js/main.js', $js);
    }
    
    private function copyMedia() {
        foreach ($this->media as $item) {
            $sourcePath = __DIR__ . '/../' . $item['file_path'];
            
            if (file_exists($sourcePath)) {
                $destPath = $this->exportPath . '/assets/img/' . basename($item['file_path']);
                copy($sourcePath, $destPath);
            }
        }
    }
    
    private function generateLanguagePages() {
        foreach ($this->project['languages'] as $lang) {
            $html = $this->buildHTML($lang);
            file_put_contents($this->exportPath . "/{$lang}/index.html", $html);
        }
    }
    
    private function buildHTML($lang) {
        $meta = $this->getPageMeta($lang);
        $theme = $this->project['theme'];
        
        $html = '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang) . '">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($meta['title'] ?? 'Portfolio') . '</title>
    <meta name="description" content="' . htmlspecialchars($meta['description'] ?? '') . '">
    ' . ($meta['image'] ?? '') . '
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="theme-' . htmlspecialchars($theme['preset'] ?? 'minimal') . '">
    ' . $this->renderLanguageSwitcher($lang) . '
    <main>
        ' . $this->renderBlocks($lang) . '
    </main>
    <script src="../assets/js/main.js"></script>
</body>
</html>';
        
        return $html;
    }
    
    private function getPageMeta($lang) {
        foreach ($this->pages as $page) {
            if ($page['is_home']) {
                $meta = json_decode($page['meta'], true);
                return $meta[$lang] ?? $meta['en'] ?? [];
            }
        }
        return [];
    }
    
    private function renderLanguageSwitcher($currentLang) {
        if (count($this->project['languages']) < 2) {
            return '';
        }
        
        $html = '<nav class="lang-switcher" aria-label="Language switcher"><ul>';
        
        foreach ($this->project['languages'] as $lang) {
            $active = $lang === $currentLang ? ' class="active"' : '';
            $html .= '<li><a href="../' . $lang . '/index.html"' . $active . '>' . strtoupper($lang) . '</a></li>';
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    private function renderBlocks($lang) {
        $html = '';
        
        foreach ($this->blocks as $block) {
            $data = json_decode($block['data'], true);
            $html .= $this->renderBlock($block['type'], $data, $lang);
        }
        
        return $html;
    }
    
    private function renderBlock($type, $data, $lang) {
        switch ($type) {
            case 'hero':
                return $this->renderHero($data, $lang);
            case 'about':
                return $this->renderAbout($data, $lang);
            case 'projects':
                return $this->renderProjects($data, $lang);
            case 'experience':
                return $this->renderExperience($data, $lang);
            case 'contact':
                return $this->renderContact($data, $lang);
            case 'footer':
                return $this->renderFooter($data, $lang);
            default:
                return '';
        }
    }
    
    private function renderHero($data, $lang) {
        $title = $this->getLocalizedText($data, 'title', $lang);
        $subtitle = $this->getLocalizedText($data, 'subtitle', $lang);
        $ctaText = $this->getLocalizedText($data, 'ctaText', $lang);
        $ctaHref = $data['ctaHref'] ?? '#';
        $avatar = $this->getMediaPath($data['avatarMediaId'] ?? null);
        
        return '<section class="hero">
            <div class="container">
                ' . ($avatar ? '<img src="' . htmlspecialchars($avatar) . '" alt="Avatar" class="hero-avatar">' : '') . '
                <h1>' . htmlspecialchars($title) . '</h1>
                <p class="hero-subtitle">' . htmlspecialchars($subtitle) . '</p>
                ' . ($ctaText ? '<a href="' . htmlspecialchars($ctaHref) . '" class="btn btn-primary">' . htmlspecialchars($ctaText) . '</a>' : '') . '
            </div>
        </section>';
    }
    
    private function renderAbout($data, $lang) {
        $headline = $this->getLocalizedText($data, 'headline', $lang);
        $content = $this->getLocalizedText($data, 'content', $lang);
        $skills = $data['skills'] ?? [];
        
        $skillsHtml = '';
        if (!empty($skills)) {
            $skillsHtml = '<ul class="skills">';
            foreach ($skills as $skill) {
                $skillsHtml .= '<li>' . htmlspecialchars($skill) . '</li>';
            }
            $skillsHtml .= '</ul>';
        }
        
        return '<section class="about">
            <div class="container">
                <h2>' . htmlspecialchars($headline) . '</h2>
                <div class="about-content">' . nl2br(htmlspecialchars($content)) . '</div>
                ' . $skillsHtml . '
            </div>
        </section>';
    }
    
    private function renderProjects($data, $lang) {
        $headline = $this->getLocalizedText($data, 'headline', $lang);
        $items = $data['items'] ?? [];
        $layout = $data['layout'] ?? 'grid-3';
        
        $html = '<section class="projects">
            <div class="container">
                <h2>' . htmlspecialchars($headline) . '</h2>
                <div class="projects-grid ' . htmlspecialchars($layout) . '">';
        
        foreach ($items as $item) {
            $title = $this->getLocalizedText($item, 'title', $lang);
            $desc = $this->getLocalizedText($item, 'desc', $lang);
            $thumb = $this->getMediaPath($item['thumbMediaId'] ?? null);
            $tags = $item['tags'] ?? [];
            
            $tagsHtml = '';
            if (!empty($tags)) {
                $tagsHtml = '<div class="project-tags">';
                foreach ($tags as $tag) {
                    $tagsHtml .= '<span class="tag">' . htmlspecialchars($tag) . '</span>';
                }
                $tagsHtml .= '</div>';
            }
            
            $html .= '<article class="project-card">
                ' . ($thumb ? '<img src="' . htmlspecialchars($thumb) . '" alt="' . htmlspecialchars($title) . '" class="project-thumb">' : '') . '
                <div class="project-content">
                    <h3>' . htmlspecialchars($title) . '</h3>
                    <p>' . htmlspecialchars($desc) . '</p>
                    ' . $tagsHtml . '
                </div>
            </article>';
        }
        
        $html .= '</div></div></section>';
        return $html;
    }
    
    private function renderExperience($data, $lang) {
        $headline = $this->getLocalizedText($data, 'headline', $lang);
        $items = $data['items'] ?? [];
        
        $html = '<section class="experience">
            <div class="container">
                <h2>' . htmlspecialchars($headline) . '</h2>
                <div class="timeline">';
        
        foreach ($items as $item) {
            $role = $this->getLocalizedText($item, 'role', $lang);
            $company = $item['company'] ?? '';
            $period = $item['period'] ?? '';
            $description = $this->getLocalizedText($item, 'description', $lang);
            
            $html .= '<article class="timeline-item">
                <h3>' . htmlspecialchars($role) . '</h3>
                <p class="timeline-meta">' . htmlspecialchars($company) . ' • ' . htmlspecialchars($period) . '</p>
                <p>' . htmlspecialchars($description) . '</p>
            </article>';
        }
        
        $html .= '</div></div></section>';
        return $html;
    }
    
    private function renderContact($data, $lang) {
        $headline = $this->getLocalizedText($data, 'headline', $lang);
        $email = $data['email'] ?? '';
        $formEnabled = $data['formEnabled'] ?? false;
        
        $html = '<section class="contact">
            <div class="container">
                <h2>' . htmlspecialchars($headline) . '</h2>';
        
        if ($formEnabled) {
            $html .= '<form action="../contact.php" method="POST" class="contact-form">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <textarea name="message" placeholder="Message" rows="5" required></textarea>
                <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                <button type="submit" class="btn btn-primary">Send</button>
            </form>';
        } else {
            $html .= '<p><a href="mailto:' . htmlspecialchars($email) . '" class="btn btn-primary">' . htmlspecialchars($email) . '</a></p>';
        }
        
        $html .= '</div></section>';
        return $html;
    }
    
    private function renderFooter($data, $lang) {
        $copyright = $this->getLocalizedText($data, 'copyright', $lang);
        $social = $data['social'] ?? [];
        
        $socialHtml = '';
        if (!empty($social)) {
            $socialHtml = '<ul class="social-links">';
            foreach ($social as $link) {
                $socialHtml .= '<li><a href="' . htmlspecialchars($link['url']) . '" target="_blank" rel="noopener">' . htmlspecialchars($link['label']) . '</a></li>';
            }
            $socialHtml .= '</ul>';
        }
        
        return '<footer class="site-footer">
            <div class="container">
                ' . $socialHtml . '
                <p>' . htmlspecialchars($copyright) . '</p>
            </div>
        </footer>';
    }
    
    private function getLocalizedText($data, $key, $lang) {
        if (isset($data[$lang][$key])) {
            return $data[$lang][$key];
        }
        if (isset($data['en'][$key])) {
            return $data['en'][$key];
        }
        return '';
    }
    
    private function getMediaPath($mediaId) {
        if (!$mediaId) return null;
        
        foreach ($this->media as $item) {
            if ($item['id'] == $mediaId) {
                return '../assets/img/' . basename($item['file_path']);
            }
        }
        
        return null;
    }
    
    private function generateCSS() {
        $theme = $this->project['theme'];
        $preset = $theme['preset'] ?? 'minimal';
        
        $colors = [
            'minimal' => ['bg' => '#ffffff', 'text' => '#1a1a1a', 'accent' => '#0066cc'],
            'bold' => ['bg' => '#000000', 'text' => '#ffffff', 'accent' => '#ff3366'],
            'creative' => ['bg' => '#f5f5f5', 'text' => '#2d2d2d', 'accent' => '#7c3aed']
        ];
        
        $themeColors = $colors[$preset] ?? $colors['minimal'];
        
        return ':root {
    --color-bg: ' . $themeColors['bg'] . ';
    --color-text: ' . $themeColors['text'] . ';
    --color-accent: ' . $themeColors['accent'] . ';
    --font-size: clamp(1rem, 2.5vw, 1.125rem);
    --spacing: clamp(1rem, 3vw, 2rem);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: var(--font-size);
    line-height: 1.6;
    color: var(--color-text);
    background: var(--color-bg);
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 var(--spacing); }

h1 { font-size: clamp(2rem, 5vw, 3.5rem); margin-bottom: 1rem; }
h2 { font-size: clamp(1.75rem, 4vw, 2.5rem); margin-bottom: 1.5rem; }
h3 { font-size: clamp(1.25rem, 3vw, 1.75rem); margin-bottom: 0.75rem; }

section { padding: clamp(3rem, 8vw, 6rem) 0; }

.btn {
    display: inline-block;
    padding: 0.75rem 2rem;
    background: var(--color-accent);
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: opacity 0.3s;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn:hover { opacity: 0.9; }

.hero {
    text-align: center;
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 1.5rem;
}

.hero-subtitle { font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.8; }

.about-content { max-width: 800px; margin-bottom: 2rem; }

.skills {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    list-style: none;
}

.skills li {
    padding: 0.5rem 1rem;
    background: var(--color-accent);
    color: white;
    border-radius: 20px;
    font-size: 0.9rem;
}

.projects-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.project-card {
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s;
}

.project-card:hover { transform: translateY(-4px); }

.project-thumb {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.project-content { padding: 1.5rem; }

.project-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.tag {
    padding: 0.25rem 0.75rem;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 12px;
    font-size: 0.85rem;
}

.timeline { max-width: 800px; }

.timeline-item {
    padding: 1.5rem;
    border-left: 3px solid var(--color-accent);
    margin-bottom: 2rem;
}

.timeline-meta { opacity: 0.7; font-size: 0.9rem; margin-bottom: 0.5rem; }

.contact-form {
    max-width: 600px;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.contact-form input,
.contact-form textarea {
    padding: 0.75rem;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
}

.site-footer {
    text-align: center;
    padding: 3rem 0;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.social-links {
    display: flex;
    justify-content: center;
    gap: 2rem;
    list-style: none;
    margin-bottom: 1rem;
}

.social-links a {
    color: var(--color-text);
    text-decoration: none;
}

.lang-switcher {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 100;
}

.lang-switcher ul {
    display: flex;
    gap: 0.5rem;
    list-style: none;
}

.lang-switcher a {
    padding: 0.5rem 0.75rem;
    background: var(--color-accent);
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.85rem;
}

.lang-switcher a.active { opacity: 0.6; }

@media (max-width: 768px) {
    .projects-grid { grid-template-columns: 1fr; }
}

@media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
}
';
    }
    
    private function generateJS() {
        return '(function() {
    "use strict";
    
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.querySelector(".contact-form");
        
        if (form) {
            form.addEventListener("submit", function(e) {
                const honeypot = form.querySelector("input[name=\'website\']");
                if (honeypot && honeypot.value !== "") {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
})();
';
    }
    
    private function generateContactHandler() {
        $hasContactForm = false;
        
        foreach ($this->blocks as $block) {
            if ($block['type'] === 'contact') {
                $data = json_decode($block['data'], true);
                if ($data['formEnabled'] ?? false) {
                    $hasContactForm = true;
                    $email = $data['email'] ?? '';
                    break;
                }
            }
        }
        
        if (!$hasContactForm) return;
        
        $php = '<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Method not allowed");
}

if (!empty($_POST["website"])) {
    http_response_code(400);
    die("Spam detected");
}

$name = htmlspecialchars($_POST["name"] ?? "");
$email = filter_var($_POST["email"] ?? "", FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars($_POST["message"] ?? "");

if (!$name || !$email || !$message) {
    http_response_code(400);
    die("Missing required fields");
}

$to = "' . addslashes($email ?? 'contact@example.com') . '";
$subject = "Contact from " . $name;
$body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
$headers = "From: $email\r\nReply-To: $email";

if (@mail($to, $subject, $body, $headers)) {
    header("Location: " . $_SERVER["HTTP_REFERER"] . "?sent=1");
} else {
    http_response_code(500);
    die("Failed to send message");
}
';
        
        file_put_contents($this->exportPath . '/contact.php', $php);
    }
    
    private function generateReadme() {
        $readme = 'Portfolio Export - ' . $this->project['name'] . '
=====================================

This is a static portfolio website generated by Portfolio Builder.

SETUP
-----
1. Upload all files to your web server
2. Ensure the web server can execute PHP (for contact form)
3. Configure email settings in contact.php if needed

STRUCTURE
---------
';
        
        foreach ($this->project['languages'] as $lang) {
            $readme .= "/{$lang}/index.html - Portfolio in " . strtoupper($lang) . "\n";
        }
        
        $readme .= '/assets/ - CSS, JS, and images
/contact.php - Contact form handler (if enabled)

CUSTOMIZATION
-------------
- Edit CSS in /assets/css/style.css
- Edit JS in /assets/js/main.js
- Replace images in /assets/img/

Generated: ' . date('Y-m-d H:i:s') . '
';
        
        file_put_contents($this->exportPath . '/README.txt', $readme);
    }
    
    private function createZip($source, $destination) {
        if (!extension_loaded('zip')) {
            throw new Exception('ZIP extension not available');
        }
        
        $zip = new ZipArchive();
        
        if (!$zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new Exception('Cannot create ZIP file');
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
        
        $this->recursiveRemoveDirectory($source);
    }
    
    private function recursiveRemoveDirectory($directory) {
        if (!is_dir($directory)) return;
        
        $files = array_diff(scandir($directory), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $directory . '/' . $file;
            is_dir($path) ? $this->recursiveRemoveDirectory($path) : unlink($path);
        }
        
        rmdir($directory);
    }
    
    private function logExport($zipSize) {
        insert('projects_export_log', [
            'project_id' => $this->projectId,
            'zip_size' => $zipSize,
            'notes' => 'Export successful'
        ]);
    }
}
