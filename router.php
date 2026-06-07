<?php
/**
 * Router for PHP built-in server (Railway deployment)
 * Dispatches requests to public/, admin/, uploads/, or install.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static assets: /assets/* → public/assets/*
if (str_starts_with($uri, '/assets/')) {
    $filePath = __DIR__ . '/public' . $uri;
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($filePath);
        return true;
    }
    http_response_code(404);
    return true;
}

// Admin routes: /admin/*
if (str_starts_with($uri, '/admin')) {
    $adminPath = __DIR__ . $uri;
    if (file_exists($adminPath) && is_file($adminPath)) {
        if (pathinfo($adminPath, PATHINFO_EXTENSION) === 'php') {
            require $adminPath;
            return true;
        }
        return false;
    }
    // /admin or /admin/ → admin/index.php
    if ($uri === '/admin' || $uri === '/admin/') {
        require __DIR__ . '/admin/index.php';
        return true;
    }
    http_response_code(404);
    return true;
}

// Uploads: serve only PDFs
if (str_starts_with($uri, '/uploads/')) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'pdf') {
        header('Content-Type: application/pdf');
        readfile($filePath);
        return true;
    }
    http_response_code(404);
    return true;
}

// Install script
if ($uri === '/install.php' || $uri === '/install') {
    if (file_exists(__DIR__ . '/install.php')) {
        require __DIR__ . '/install.php';
        return true;
    }
}

// Public PHP pages: /test.php, /thankyou.php
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicFile) && is_file($publicFile)) {
    if (pathinfo($publicFile, PATHINFO_EXTENSION) === 'php') {
        require $publicFile;
        return true;
    }
    return false;
}

// Default: application form
require __DIR__ . '/public/index.php';
return true;
