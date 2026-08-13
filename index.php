<?php
// Root router for PHP built-in server (php -S localhost:3031)

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Route /uploads/ requests directly to uploaded files
if (strpos($uri, '/uploads/') === 0) {
    $uploadFile = __DIR__ . $uri;
    if (!file_exists($uploadFile)) {
        $uploadFile = __DIR__ . '/backend' . $uri;
    }
    if (file_exists($uploadFile) && !is_dir($uploadFile)) {
        $ext = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        $mime = $mimeTypes[$ext] ?? mime_content_type($uploadFile) ?: 'application/octet-stream';
        header("Content-Type: $mime");
        header("Content-Length: " . filesize($uploadFile));
        readfile($uploadFile);
        exit;
    }
}

// 2. Route API requests to backend MVC framework
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/backend/public/index.php';
    exit;
}

// 2. Serve frontend production static files
$distFile = __DIR__ . '/frontend/dist' . $uri;
$rootFile = __DIR__ . $uri;
$targetFile = (file_exists($distFile) && !is_dir($distFile)) ? $distFile : ((file_exists($rootFile) && !is_dir($rootFile)) ? $rootFile : null);

if ($uri !== '/' && $targetFile) {
    $mime = mime_content_type($targetFile);
    if (str_ends_with($uri, '.css')) $mime = 'text/css';
    if (str_ends_with($uri, '.js')) $mime = 'application/javascript';
    if (str_ends_with($uri, '.png')) $mime = 'image/png';
    if (str_ends_with($uri, '.jpg')) $mime = 'image/jpeg';
    header("Content-Type: $mime");
    readfile($targetFile);
    exit;
}

// 3. Fallback to built React Single Page Application (frontend/dist/index.html or backend/index.html)
$distIndex = __DIR__ . '/frontend/dist/index.html';
$rootIndex = __DIR__ . '/backend/index.html';
$targetIndex = file_exists($distIndex) ? $distIndex : (file_exists($rootIndex) ? $rootIndex : null);

if ($targetIndex) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Content-Type: text/html; charset=utf-8');
    readfile($targetIndex);
    exit;
}

// 4. If dist is not built, forward to backend API info page
require_once __DIR__ . '/backend/public/index.php';
