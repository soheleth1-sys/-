<?php
header('Content-Type: application/json; charset=utf-8');

function respond($success, $message, $url = null, $status = 200) {
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'url' => $url
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'POST request required.', null, 405);
}

$name = trim($_POST['siteName'] ?? '');
if (!preg_match('/\A[a-zA-Z0-9_-]{1,40}\z/', $name)) {
    respond(false, 'Invalid website name.', null, 400);
}

if (!isset($_FILES['htmlFile'])) {
    respond(false, 'Please select an HTML file.', null, 400);
}

$file = $_FILES['htmlFile'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Upload failed. Check PHP upload limits.', null, 400);
}
if ($file['size'] < 1 || $file['size'] > 2 * 1024 * 1024) {
    respond(false, 'File must be between 1 byte and 2 MB.', null, 413);
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['html', 'htm'], true)) {
    respond(false, 'Only .html and .htm files are allowed.', null, 400);
}
if (!is_uploaded_file($file['tmp_name'])) {
    respond(false, 'Invalid upload.', null, 400);
}

$root = __DIR__ . DIRECTORY_SEPARATOR . 'sites';
$siteDir = $root . DIRECTORY_SEPARATOR . $name;

if (!is_dir($root) && !mkdir($root, 0755, true) && !is_dir($root)) {
    respond(false, 'Could not create sites directory. Check hosting permissions.', null, 500);
}

if (file_exists($siteDir)) {
    respond(false, 'This website name is already taken. Choose another name.', null, 409);
}
if (!mkdir($siteDir, 0755)) {
    respond(false, 'Could not create website folder. Check hosting permissions.', null, 500);
}

$destination = $siteDir . DIRECTORY_SEPARATOR . 'index.html';
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    rmdir($siteDir);
    respond(false, 'Could not save uploaded HTML file.', null, 500);
}

/* Build URL from the current hosting origin and installation path. */
$host = $_SERVER['HTTP_HOST'] ?? '';
if (!$host || !preg_match('/\A[a-zA-Z0-9.-]+(?::[0-9]{1,5})?\z/', $host)) {
    unlink($destination);
    rmdir($siteDir);
    respond(false, 'Invalid hosting domain.', null, 500);
}

$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);
$scheme = $isHttps ? 'https' : 'http';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/deploy.php');
$basePath = rtrim(dirname($scriptName), '/.');
if ($basePath === '/') $basePath = '';

$url = $scheme . '://' . $host . $basePath .
       '/sites/' . rawurlencode($name) . '/';

respond(true, 'Website published successfully.', $url);
?>
