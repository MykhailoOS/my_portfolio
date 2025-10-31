<?php

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../lib/export.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    errorResponse('Action required', 400);
}

if ($method === 'POST' && !in_array($action, ['project.get'])) {
    validateCsrf();
}

try {
    switch ($action) {
        case 'project.create':
            handleProjectCreate();
            break;
        case 'project.get':
            handleProjectGet();
            break;
        case 'project.update':
            handleProjectUpdate();
            break;
        case 'page.add':
            handlePageAdd();
            break;
        case 'page.update':
            handlePageUpdate();
            break;
        case 'page.delete':
            handlePageDelete();
            break;
        case 'block.add':
            handleBlockAdd();
            break;
        case 'block.update':
            handleBlockUpdate();
            break;
        case 'block.reorder':
            handleBlockReorder();
            break;
        case 'block.delete':
            handleBlockDelete();
            break;
        case 'media.upload':
            handleMediaUpload();
            break;
        case 'export.zip':
            handleExportZip();
            break;
        default:
            errorResponse('Unknown action', 404);
    }
} catch (Exception $e) {
    errorResponse($e->getMessage(), 500);
}

function handleProjectCreate() {
    $name = $_POST['name'] ?? 'Untitled Project';
    $languages = json_decode($_POST['languages'] ?? '["en"]', true);
    $themePreset = $_POST['theme_preset'] ?? 'minimal';
    
    $slug = generateSlug($name);
    $counter = 1;
    
    while (fetchOne("SELECT id FROM projects WHERE slug = ?", [$slug])) {
        $slug = generateSlug($name) . '-' . $counter++;
    }
    
    $theme = [
        'preset' => $themePreset,
        'colors' => [],
        'fontScale' => 'md',
        'spacingScale' => 'md'
    ];
    
    $projectId = insert('projects', [
        'name' => $name,
        'slug' => $slug,
        'languages' => json_encode($languages),
        'theme' => json_encode($theme)
    ]);
    
    $pageId = insert('pages', [
        'project_id' => $projectId,
        'path' => '/',
        'is_home' => 1,
        'meta' => json_encode([]),
        'order' => 0
    ]);
    
    $defaultBlocks = [
        ['type' => 'hero', 'order' => 0, 'data' => json_encode([
            'en' => ['title' => 'Your Name', 'subtitle' => 'Your Role', 'ctaText' => 'Get in touch', 'ctaHref' => '#contact'],
            'uk' => ['title' => 'Ваше ім\'я', 'subtitle' => 'Ваша роль', 'ctaText' => 'Зв\'язатися', 'ctaHref' => '#contact'],
            'ru' => ['title' => 'Ваше имя', 'subtitle' => 'Ваша роль', 'ctaText' => 'Связаться', 'ctaHref' => '#contact'],
            'pl' => ['title' => 'Twoje imię', 'subtitle' => 'Twoja rola', 'ctaText' => 'Skontaktuj się', 'ctaHref' => '#contact']
        ])],
        ['type' => 'about', 'order' => 1, 'data' => json_encode([
            'en' => ['headline' => 'About Me', 'content' => 'Tell your story here...'],
            'uk' => ['headline' => 'Про мене', 'content' => 'Розкажіть свою історію тут...'],
            'ru' => ['headline' => 'Обо мне', 'content' => 'Расскажите свою историю здесь...'],
            'pl' => ['headline' => 'O mnie', 'content' => 'Opowiedz swoją historię tutaj...'],
            'skills' => []
        ])],
        ['type' => 'projects', 'order' => 2, 'data' => json_encode([
            'en' => ['headline' => 'My Work'],
            'uk' => ['headline' => 'Мої роботи'],
            'ru' => ['headline' => 'Мои работы'],
            'pl' => ['headline' => 'Moja praca'],
            'items' => [],
            'layout' => 'grid-3'
        ])],
        ['type' => 'contact', 'order' => 3, 'data' => json_encode([
            'en' => ['headline' => 'Get In Touch'],
            'uk' => ['headline' => 'Зв\'язатися'],
            'ru' => ['headline' => 'Связаться'],
            'pl' => ['headline' => 'Skontaktuj się'],
            'email' => 'hello@example.com',
            'formEnabled' => false
        ])],
        ['type' => 'footer', 'order' => 4, 'data' => json_encode([
            'en' => ['copyright' => '© 2024 All rights reserved'],
            'uk' => ['copyright' => '© 2024 Всі права захищені'],
            'ru' => ['copyright' => '© 2024 Все права защищены'],
            'pl' => ['copyright' => '© 2024 Wszystkie prawa zastrzeżone'],
            'social' => []
        ])]
    ];
    
    foreach ($defaultBlocks as $block) {
        insert('blocks', [
            'project_id' => $projectId,
            'page_id' => $pageId,
            'type' => $block['type'],
            'order' => $block['order'],
            'data' => $block['data']
        ]);
    }
    
    jsonResponse(['id' => $projectId, 'slug' => $slug]);
}

function handleProjectGet() {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        errorResponse('Project ID required');
    }
    
    $project = fetchOne("SELECT * FROM projects WHERE id = ?", [$id]);
    
    if (!$project) {
        errorResponse('Project not found', 404);
    }
    
    $project['languages'] = json_decode($project['languages'], true);
    $project['theme'] = json_decode($project['theme'], true);
    
    $pages = fetchAll("SELECT * FROM pages WHERE project_id = ? ORDER BY `order`", [$id]);
    
    foreach ($pages as &$page) {
        $page['meta'] = json_decode($page['meta'], true);
    }
    
    $blocks = fetchAll("SELECT * FROM blocks WHERE project_id = ? ORDER BY page_id, `order`", [$id]);
    
    foreach ($blocks as &$block) {
        $block['data'] = json_decode($block['data'], true);
    }
    
    $media = fetchAll("SELECT * FROM media WHERE project_id = ?", [$id]);
    
    foreach ($media as &$item) {
        $item['alt'] = json_decode($item['alt'], true);
    }
    
    jsonResponse([
        'project' => $project,
        'pages' => $pages,
        'blocks' => $blocks,
        'media' => $media
    ]);
}

function handleProjectUpdate() {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? null;
    $languages = $_POST['languages'] ?? null;
    $theme = $_POST['theme'] ?? null;
    
    if (!$id) {
        errorResponse('Project ID required');
    }
    
    $updates = [];
    
    if ($name !== null) {
        $updates['name'] = $name;
    }
    
    if ($languages !== null) {
        $updates['languages'] = $languages;
    }
    
    if ($theme !== null) {
        $updates['theme'] = $theme;
    }
    
    if (empty($updates)) {
        errorResponse('No updates provided');
    }
    
    update('projects', $updates, 'id = :id', ['id' => $id]);
    
    jsonResponse(['success' => true]);
}

function handlePageAdd() {
    $projectId = $_POST['project_id'] ?? null;
    $path = $_POST['path'] ?? '/';
    $meta = $_POST['meta'] ?? '{}';
    
    if (!$projectId) {
        errorResponse('Project ID required');
    }
    
    $maxOrder = fetchOne(
        "SELECT MAX(`order`) as max_order FROM pages WHERE project_id = ?",
        [$projectId]
    );
    
    $order = ($maxOrder['max_order'] ?? -1) + 1;
    
    $pageId = insert('pages', [
        'project_id' => $projectId,
        'path' => $path,
        'is_home' => 0,
        'meta' => $meta,
        'order' => $order
    ]);
    
    jsonResponse(['id' => $pageId]);
}

function handlePageUpdate() {
    $id = $_POST['id'] ?? null;
    $meta = $_POST['meta'] ?? null;
    
    if (!$id) {
        errorResponse('Page ID required');
    }
    
    if ($meta !== null) {
        update('pages', ['meta' => $meta], 'id = :id', ['id' => $id]);
    }
    
    jsonResponse(['success' => true]);
}

function handlePageDelete() {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        errorResponse('Page ID required');
    }
    
    delete('pages', 'id = :id', ['id' => $id]);
    
    jsonResponse(['success' => true]);
}

function handleBlockAdd() {
    $projectId = $_POST['project_id'] ?? null;
    $pageId = $_POST['page_id'] ?? null;
    $type = $_POST['type'] ?? null;
    $data = $_POST['data'] ?? '{}';
    
    if (!$projectId || !$pageId || !$type) {
        errorResponse('Missing required fields');
    }
    
    $validTypes = ['hero', 'about', 'projects', 'experience', 'contact', 'footer'];
    
    if (!in_array($type, $validTypes)) {
        errorResponse('Invalid block type');
    }
    
    $maxOrder = fetchOne(
        "SELECT MAX(`order`) as max_order FROM blocks WHERE page_id = ?",
        [$pageId]
    );
    
    $order = ($maxOrder['max_order'] ?? -1) + 1;
    
    $blockId = insert('blocks', [
        'project_id' => $projectId,
        'page_id' => $pageId,
        'type' => $type,
        'order' => $order,
        'data' => $data
    ]);
    
    jsonResponse(['id' => $blockId]);
}

function handleBlockUpdate() {
    $id = $_POST['id'] ?? null;
    $data = $_POST['data'] ?? null;
    
    if (!$id || $data === null) {
        errorResponse('Block ID and data required');
    }
    
    update('blocks', ['data' => $data], 'id = :id', ['id' => $id]);
    
    jsonResponse(['success' => true]);
}

function handleBlockReorder() {
    $blocks = json_decode($_POST['blocks'] ?? '[]', true);
    
    if (empty($blocks)) {
        errorResponse('Blocks array required');
    }
    
    $pdo = getDbConnection();
    $pdo->beginTransaction();
    
    try {
        foreach ($blocks as $item) {
            if (!isset($item['id']) || !isset($item['order'])) continue;
            
            update('blocks', ['order' => $item['order']], 'id = :id', ['id' => $item['id']]);
        }
        
        $pdo->commit();
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function handleBlockDelete() {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        errorResponse('Block ID required');
    }
    
    delete('blocks', 'id = :id', ['id' => $id]);
    
    jsonResponse(['success' => true]);
}

function handleMediaUpload() {
    if (!isset($_FILES['file'])) {
        errorResponse('No file uploaded');
    }
    
    $projectId = $_POST['project_id'] ?? null;
    
    if (!$projectId) {
        errorResponse('Project ID required');
    }
    
    $file = $_FILES['file'];
    
    $validation = validateImage($file);
    
    if (!$validation['valid']) {
        errorResponse($validation['error']);
    }
    
    $uploadDir = __DIR__ . '/uploads/' . $projectId;
    ensureDir($uploadDir);
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        errorResponse('Failed to save file');
    }
    
    $relativePath = 'uploads/' . $projectId . '/' . $filename;
    
    $mediaId = insert('media', [
        'project_id' => $projectId,
        'file_path' => $relativePath,
        'alt' => json_encode([]),
        'mime' => $file['type'],
        'size' => $file['size']
    ]);
    
    jsonResponse([
        'id' => $mediaId,
        'file_path' => $relativePath,
        'url' => '/' . $relativePath
    ]);
}

function handleExportZip() {
    $projectId = $_POST['project_id'] ?? null;
    
    if (!$projectId) {
        errorResponse('Project ID required');
    }
    
    $exporter = new PortfolioExporter($projectId);
    $zipPath = $exporter->export();
    
    if (!file_exists($zipPath)) {
        errorResponse('Export failed', 500);
    }
    
    $project = fetchOne("SELECT name FROM projects WHERE id = ?", [$projectId]);
    $filename = generateSlug($project['name'] ?? 'portfolio') . '.zip';
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($zipPath));
    
    readfile($zipPath);
    unlink($zipPath);
    
    exit;
}
