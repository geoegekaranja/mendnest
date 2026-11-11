<?php
/* Setup check for the Mend Nest site.
   Open it in your browser:   http://localhost/mendnest/check_setup.php
   It only works on your own computer.  DELETE THIS FILE when you are done with it. */

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('This page can only be opened on your own computer.');
}
error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = __DIR__;

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function row(string $status, string $label, string $detail = ''): void {
    $colour = ['ok' => '#198754', 'warn' => '#b58100', 'bad' => '#dc3545'][$status] ?? '#333';
    $word   = ['ok' => 'OK', 'warn' => 'CHECK', 'bad' => 'PROBLEM'][$status] ?? '';
    echo '<tr><td style="color:' . $colour . ';font-weight:700;white-space:nowrap">' . $word . '</td>'
       . '<td>' . e($label) . '</td><td>' . e($detail) . '</td></tr>';
}
function begin(string $title): void {
    echo '<h2>' . e($title) . '</h2><table>';
}
function finish(): void {
    echo '</table>';
}
function tail_file(string $path, int $bytes = 8000): ?string {
    if (!is_file($path) || !is_readable($path)) {
        return null;
    }
    $size = filesize($path);
    $fh   = fopen($path, 'rb');
    if (!$fh) {
        return null;
    }
    fseek($fh, max(0, $size - $bytes));
    $data = (string)stream_get_contents($fh);
    fclose($fh);
    $lines = preg_split('/\R/', trim($data));
    return implode("\n", array_slice($lines, -14));
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Mend Nest setup check</title>
<style>
body { font-family: Segoe UI, Arial, sans-serif; margin: 24px; max-width: 1000px; color: #222; }
h1 { margin-bottom: 4px; } h2 { margin: 26px 0 6px; border-bottom: 2px solid #198754; padding-bottom: 3px; }
table { border-collapse: collapse; width: 100%; font-size: 14px; }
td { border-bottom: 1px solid #e5e5e5; padding: 5px 8px; vertical-align: top; }
pre { background: #f6f6f6; padding: 10px; overflow-x: auto; font-size: 12px; }
.note { background: #fff8e1; border-left: 4px solid #b58100; padding: 8px 12px; }
</style>
</head>
<body>
<h1>Mend Nest setup check</h1>
<p class="note">Delete <strong>check_setup.php</strong> from the project folder when you are finished.</p>

<?php
/* ------------------------------------------------------------------ 1. PHP */
begin('1. PHP');
row(version_compare(PHP_VERSION, '8.0.0', '>=') ? 'ok' : 'bad', 'PHP version', PHP_VERSION . ' (the site needs 8.0 or newer)');
foreach (['mysqli', 'mbstring'] as $ext) {
    row(extension_loaded($ext) ? 'ok' : 'bad', 'Extension ' . $ext, extension_loaded($ext) ? 'loaded' : 'NOT loaded');
}
row('ok', 'display_errors', ini_get('display_errors') === '' ? 'off (errors are hidden, so a broken page can look blank)' : (string)ini_get('display_errors'));
row('ok', 'error_log setting', ini_get('error_log') ?: '(not set)');
finish();

/* ------------------------------------------------------------------ 2. Files */
begin('2. Files that must exist (and not be empty)');
// Every .php file in the project, so a file saved in the wrong folder can be spotted
$skipDirs = ['uploads', '.git', 'node_modules', 'vendor'];
$allFiles = [];
$scan = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    function ($current, $key, $it) use ($skipDirs) {
        if ($it->hasChildren()) {
            return !in_array($current->getFilename(), $skipDirs, true);
        }
        return strtolower($current->getExtension()) === 'php';
    }
));
foreach ($scan as $scanned) {
    $allFiles[] = str_replace('\\', '/', substr($scanned->getPathname(), strlen($root) + 1));
}

$files = [
    'login.php', 'logout.php', 'request_care.php', 'index.php',
    'includes/auth.php', 'includes/accounts.php', 'includes/caregivers.php', 'includes/caregiver_picker.php',
    'includes/dashboard_header.php', 'includes/dashboard_footer.php', 'includes/admin_header.php',
    'includes/admin_footer.php', 'includes/header.php', 'includes/footer.php',
    'database/connection.php', 'css/dashboard.css',
    'admin/admindashboard.php', 'admin/caregivers.php', 'admin/bookings.php', 'admin/add_admin.php', 'admin/patients.php',
    'caregiver/dashboard.php', 'caregiver/profile.php', 'caregiver/assignments.php',
    'patient/dashboard.php', 'patient/book.php', 'patient/my_bookings.php', 'patient/caregiver.php',
    'pages/consultation.php',
];
$fileProblems = 0;
foreach ($files as $f) {
    $path = $root . '/' . $f;
    if (!is_file($path)) {
        $hints = [];
        foreach ($allFiles as $candidate) {
            $b = basename($candidate);
            if (!in_array($candidate, $files, true)
                && (strcasecmp($b, basename($f)) === 0 || strcasecmp($b, str_replace('_', ' ', basename($f))) === 0)) {
                $hints[] = $candidate;
            }
        }
        row('bad', $f, 'MISSING' . ($hints ? '. A file with this name exists at: ' . implode(', ', $hints) . '. Move it, or rename it, to ' . $f : ''));
        $fileProblems++;
    } elseif (filesize($path) < 40) {
        row('bad', $f, 'EMPTY or almost empty (' . filesize($path) . ' bytes). Save the file again.');
        $fileProblems++;
    }
}
if ($fileProblems === 0) {
    row('ok', 'All ' . count($files) . ' files', 'present and not empty');
}
$uploads = $root . '/uploads/caregivers';
if (!is_dir($uploads)) {
    row('warn', 'uploads/caregivers', 'folder is missing: caregiver photos cannot be saved (it is created on the first upload if the uploads folder is writable)');
} else {
    row(is_writable($uploads) ? 'ok' : 'bad', 'uploads/caregivers', is_writable($uploads) ? 'exists and is writable' : 'exists but is NOT writable');
}
finish();

/* ------------------------------------------------------------------ 2b. Versions */
begin('2b. Are the main files the latest version?');
// file => [text it must contain, text it must NOT contain]
$versions = [
    'includes/auth.php'             => [['verify_account', 'ROLE_TABLES'], []],
    'includes/dashboard_header.php' => [['Caregiver Portal', "'patients.php'"], []],
    'includes/accounts.php'         => [['email_in_use', 'save_uploaded_photo'], []],
    'includes/caregivers.php'       => [['is_active = 1'], []],
    'login.php'                     => [['role_valid', 'data-pw'], ['is_admin']],
    'logout.php'                    => [["/index.php"], []],
    'admin/admindashboard.php'      => [['Available Caregivers', "admin_link('patients.php')"], []],
    'patient/book.php'              => [['requested_caregiver_id'], []],
    'pages/consultation.php'        => [['does not book a caregiver'], ['INSERT INTO bookings']],
    'css/dashboard.css'             => [['.caregiver-area'], []],
];
$outdated = 0;
foreach ($versions as $f => [$must, $mustNot]) {
    $path = $root . '/' . $f;
    if (!is_file($path)) {
        continue;                                   // already reported as MISSING above
    }
    $code      = (string)file_get_contents($path);
    $lacking   = array_values(array_filter($must,    fn($m) => strpos($code, $m) === false));
    $forbidden = array_values(array_filter($mustNot, fn($m) => strpos($code, $m) !== false));
    if ($lacking || $forbidden) {
        $outdated++;
        row('bad', $f, 'OLD VERSION. '
            . ($lacking ? 'Missing: ' . implode(', ', $lacking) . '. ' : '')
            . ($forbidden ? 'Still contains: ' . implode(', ', $forbidden) . '. ' : '')
            . 'Replace it with the latest file.');
    }
}
if ($outdated === 0) {
    row('ok', 'Main files', 'all are the latest version');
}
if (is_dir($root . '/caregivers')) {
    row('warn', 'Folder "caregivers"', 'A folder with this name (plural) exists in the project root. The caregiver pages belong in "caregiver" (singular). Move any real files from it into "caregiver", then delete the "caregivers" folder.');
}
finish();

/* ------------------------------------------------------------------ 3. Syntax */
begin('3. PHP syntax in every .php file');
$skip    = ['uploads', '.git', 'node_modules', 'vendor'];
$checked = 0;
$broken  = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    function ($current, $key, $it) use ($skip) {
        if ($it->hasChildren()) {
            return !in_array($current->getFilename(), $skip, true);
        }
        return strtolower($current->getExtension()) === 'php';
    }
));
foreach ($iterator as $file) {
    $rel  = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    $code = (string)file_get_contents($file->getPathname());
    $checked++;
    if ($code === '') {
        row('warn', $rel, 'empty file');
        continue;
    }
    try {
        token_get_all($code, TOKEN_PARSE);
    } catch (ParseError $pe) {
        $broken++;
        row('bad', $rel, 'Syntax error on line ' . $pe->getLine() . ': ' . $pe->getMessage());
    }
}
if ($broken === 0) {
    row('ok', $checked . ' files checked', 'no syntax errors');
}
finish();

/* ------------------------------------------------------------------ 4. Database */
begin('4. Database');
$expected = [
    'admins'     => ['id', 'role', 'name', 'email', 'password', 'is_active', 'failed_attempts', 'locked_until', 'last_login_at'],
    'caregivers' => ['id', 'role', 'caregiver_code', 'name', 'email', 'password', 'phone', 'gender', 'specialization',
                     'qualification', 'license_number', 'years_experience', 'languages', 'areas_served', 'bio', 'photo',
                     'is_available', 'is_active', 'failed_attempts', 'locked_until', 'last_login_at'],
    'patients'   => ['id', 'role', 'name', 'email', 'password', 'phone', 'is_active', 'failed_attempts', 'locked_until', 'last_login_at'],
    'bookings'   => ['id', 'patient_name', 'service', 'appointment_date', 'message', 'patient_id', 'status', 'created_at',
                     'caregiver_id', 'package', 'phone', 'preferred_time', 'duration', 'service_address', 'city',
                     'requested_caregiver_id'],
    'consultations' => ['id', 'status', 'admin_notes'],
];
$roles = ['admins' => 'admin', 'caregivers' => 'caregiver', 'patients' => 'patient'];

try {
    require_once $root . '/database/connection.php';
    if (!isset($conn) || !($conn instanceof mysqli)) {
        row('bad', 'connection.php', 'it did not create a $conn database connection');
    } else {
        $dbName = (string)$conn->query('SELECT DATABASE()')->fetch_row()[0];
        row('ok', 'Connected to database', $dbName);

        foreach ($expected as $table => $needed) {
            $stmt = $conn->prepare("SELECT COLUMN_NAME, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
            $stmt->bind_param('ss', $dbName, $table);
            $stmt->execute();
            $cols = [];
            foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $c) {
                $cols[$c['COLUMN_NAME']] = (string)$c['EXTRA'];
            }
            $stmt->close();

            if (!$cols) {
                row('bad', "Table $table", 'does not exist. Run roles_setup.sql');
                continue;
            }
            $missing = array_values(array_diff($needed, array_keys($cols)));
            if ($missing) {
                row('bad', "Table $table", 'missing columns: ' . implode(', ', $missing) . '. Run roles_setup.sql');
            } else {
                row('ok', "Table $table", 'all expected columns present');
            }
            if (isset($roles[$table]) && isset($cols['id']) && stripos($cols['id'], 'auto_increment') === false) {
                row('bad', "Table $table: id", 'is not AUTO_INCREMENT. Run roles_setup.sql (step 3b)');
            }
            if (isset($roles[$table]) && !$missing) {
                $n = (int)$conn->query("SELECT COUNT(*) FROM `$table`")->fetch_row()[0];
                $wrong = $conn->prepare("SELECT COUNT(*) FROM `$table` WHERE role <> ?");
                $wrong->bind_param('s', $roles[$table]);
                $wrong->execute();
                $badRole = (int)$wrong->get_result()->fetch_row()[0];
                $wrong->close();
                row($badRole === 0 ? 'ok' : 'bad', "Table $table: rows", $n . ' account(s)' . ($badRole ? ", $badRole with an empty or wrong role" : ', all with role ' . $roles[$table]));
            }
        }
    }
} catch (Throwable $ex) {
    row('bad', 'Database check failed', $ex->getMessage());
}
finish();

/* ------------------------------------------------------------------ 5. Logs */
begin('5. Latest lines from the error logs');
$logs = array_unique(array_filter([
    (string)ini_get('error_log'),
    dirname((string)($_SERVER['DOCUMENT_ROOT'] ?? '')) . '/php/logs/php_error_log',
    dirname((string)($_SERVER['DOCUMENT_ROOT'] ?? '')) . '/apache/logs/error.log',
]));
$shown = false;
foreach ($logs as $log) {
    $text = tail_file($log);
    if ($text !== null) {
        $shown = true;
        echo '<tr><td colspan="3"><strong>' . e($log) . '</strong><pre>' . e($text) . '</pre></td></tr>';
    }
}
if (!$shown) {
    row('warn', 'No readable error log found', 'In the XAMPP Control Panel click Logs next to Apache > error.log');
}
finish();
?>
<p style="margin-top:28px">Send me a screenshot of this page, or just the lines marked PROBLEM or CHECK.</p>
</body>
</html>
