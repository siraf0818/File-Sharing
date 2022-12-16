<?php
error_reporting(error_reporting() & ~E_NOTICE);
session_start();
//Security options
if (!isset($_SESSION['user']) || (trim($_SESSION['user']) == '')) {
    $allow_delete = false; // Set to false to disable delete button and delete POST request.
} else {
    $allow_delete = true;
}

$allow_upload = true; // Set to true to allow upload files
$allow_direct_link = true; // Set to false to only allow downloads and not direct link
$allow_show_folders = false; // Set to false to hide all subdirectories
$disallowed_extensions = ['php', 'js', 'css', 'html'];  // must be an array. Extensions disallowed to be uploaded
$hidden_extensions = ['php', 'js', 'css', 'html']; // must be an array of lowercase file extensions. Extensions hidden in directory index

$tmp_dir = dirname($_SERVER['SCRIPT_FILENAME']);
if (DIRECTORY_SEPARATOR === '\\') $tmp_dir = str_replace('/', DIRECTORY_SEPARATOR, $tmp_dir);
$tmp = get_absolute_path($tmp_dir . '/' . $_REQUEST['file']);
if ($tmp === false)
    err(404, 'File or Directory Not Found');
if (substr($tmp, 0, strlen($tmp_dir)) !== $tmp_dir)
    err(403, "Forbidden");
if (strpos($_REQUEST['file'], DIRECTORY_SEPARATOR) === 0)
    err(403, "Forbidden");

$file = $_REQUEST['file'] ?: '.';
if ($_GET['do'] == 'list') {
    if (is_dir($file)) {
        $directory = $file;
        $result = [];
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $entry) if (!is_entry_ignored($entry, $allow_show_folders, $hidden_extensions)) {
            $i = $directory . '/' . $entry;
            $stat = stat($i);
            $result[] = [
                'mtime' => $stat['mtime'],
                'size' => $stat['size'],
                'name' => basename($i),
                'path' => preg_replace('@^\./@', '', $i),
                'is_dir' => is_dir($i),
                'is_deleteable' => $allow_delete && ((!is_dir($i) && is_writable($directory)) ||
                    (is_dir($i) && is_writable($directory) && is_recursively_deleteable($i))),
                'is_readable' => is_readable($i),
                'is_writable' => is_writable($i),
                'is_executable' => is_executable($i),
            ];
        }
    } else {
        err(412, "Not a Directory");
    }
    echo json_encode(['success' => true, 'is_writable' => is_writable($file), 'results' => $result]);
    exit;
} elseif ($_POST['do'] == 'delete') {
    if ($allow_delete) {
        rmrf($file);
    }
    exit;
} elseif ($_POST['do'] == 'upload' && $allow_upload) {
    foreach ($disallowed_extensions as $ext)
        if (preg_match(sprintf('/\.%s$/', preg_quote($ext)), $_FILES['file_data']['name']))
            err(403, "Files of this type are not allowed.");
    $res = move_uploaded_file($_FILES['file_data']['tmp_name'], $file . '/' . $_FILES['file_data']['name']);
    exit;
} elseif ($_GET['do'] == 'download') {
    $filename = basename($file);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    header('Content-Type: ' . finfo_file($finfo, $file));
    header('Content-Length: ' . filesize($file));
    header(sprintf(
        'Content-Disposition: attachment; filename=%s',
        strpos('MSIE', $_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\""
    ));
    ob_flush();
    readfile($file);
    exit;
}

function is_entry_ignored($entry, $allow_show_folders, $hidden_extensions)
{
    if ($entry === basename(__FILE__)) {
        return true;
    }
    if (is_dir($entry) && !$allow_show_folders) {
        return true;
    }
    $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
    if (in_array($ext, $hidden_extensions)) {
        return true;
    }
    return false;
}

function rmrf($dir)
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file)
            rmrf("$dir/$file");
        rmdir($dir);
    } else {
        unlink($dir);
    }
}

function is_recursively_deleteable($d)
{
    $stack = [$d];
    while ($dir = array_pop($stack)) {
        if (!is_readable($dir) || !is_writable($dir))
            return false;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) if (is_dir($file)) {
            $stack[] = "$dir/$file";
        }
    }
    return true;
}

function get_absolute_path($path)
{
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $parts = explode(DIRECTORY_SEPARATOR, $path);
    $absolutes = [];
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return implode(DIRECTORY_SEPARATOR, $absolutes);
}

function err($code, $msg)
{
    http_response_code($code);
    echo json_encode(['error' => ['code' => intval($code), 'msg' => $msg]]);
    exit;
}

function asBytes($ini_v)
{
    $ini_v = trim($ini_v);
    $s = ['g' => 1 << 30, 'm' => 1 << 20, 'k' => 1 << 10];
    return intval($ini_v) * ($s[strtolower(substr($ini_v, -1))] ?: 1);
}
$MAX_UPLOAD_SIZE = min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));
