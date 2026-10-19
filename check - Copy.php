<?php
/* TEMPORARY diagnostic page.
   Open http://localhost/mendnest/check.php, read the results, then DELETE this file. */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Local access only.');
}

$root = __DIR__;

function row(bool $ok, string $label, string $detail = ''): void {
    echo '<tr><td>' . ($ok ? '✅' : '❌') . '</td><td>' . htmlspecialchars($label) .
         '</td><td>' . htmlspecialchars($detail) . '</td></tr>';
}

/* Look for a misplaced file with the same name anywhere in the project */
function find_elsewhere(string $root, string $basename): array {
    $found = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile() && strcasecmp($f->getFilename(), $basename) === 0) {
            $found[] = str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
        }
    }
    return $found;
}

echo '<!DOCTYPE html><meta charset="utf-8"><title>Site check</title>
<style>body{font-family:sans-serif;margin:30px}td{padding:4px 14px;border-bottom:1px solid #ddd;vertical-align:top}</style>
<h2>Mend Nest site check</h2><h3>1. Files</h3><table>';

$files = [
    'includes/auth.php', 'includes/header.php', 'includes/footer.php',
    'includes/admin_header.php', 'includes/admin_footer.php',
    'includes/patient_header.php', 'includes/patient_footer.php',
    'database/connection.php', 'css/style.css', 'assets/nurse.jpg',
    'index.php', 'login.php', 'logout.php',
    'pages/consultation.php', 'pages/contact.php', 'pages/blog.php', 'pages/services.php',
    'pages/about.php', 'pages/submit_testimonial.php',
    'admin/dashboard.php', 'admin/bookings.php', 'admin/consultations.php', 'admin/testimonials.php',
    'patient/dashboard.php', 'patient/book.php', 'patient/my_bookings.php',
];
foreach ($files as $f) {
    if (is_file("$root/$f")) {
        row(true, $f, 'found');
    } else {
        $else = find_elsewhere($root, basename($f));
        row(false, $f, $else ? 'MISSING here, but found at: ' . implode(', ', $else) . '  → move it to ' . $f : 'MISSING (not found anywhere)');
    }
}
echo '</table>';

echo '<h3>2. Web address</h3><table>';
if (is_file("$root/includes/auth.php")) {
    require_once "$root/includes/auth.php";
    row(BASE_URL !== '' || SITE_FOLDER === '', 'BASE_URL', BASE_URL === '' ? '(empty: only correct if the site is at the domain root)' : BASE_URL);
    $css = BASE_URL . '/css/style.css';
    echo '<tr><td></td><td>Stylesheet address</td><td><a href="' . htmlspecialchars($css) . '" target="_blank">' .
         htmlspecialchars($css) . '</a> (click: you should see CSS text, not "Not Found")</td></tr>';
} else {
    row(false, 'includes/auth.php', 'missing, so nothing else can be checked');
}
echo '</table>';

echo '<h3>3. Database</h3><table>';
if (is_file("$root/database/connection.php")) {
    require_once "$root/database/connection.php";   // stops with an error message if MySQL / the database name is wrong
    row(true, 'Database connection', 'ok');

    $need = [
        'patients'         => ['id', 'name', 'email', 'password', 'role', 'is_active', 'failed_attempts', 'locked_until'],
        'bookings'         => ['id', 'patient_id', 'patient_name', 'service', 'appointment_date', 'message', 'status'],
        'testimonials'     => ['id', 'name', 'message', 'is_approved', 'created_at'],
        'consultations'    => ['id', 'status'],
        'contact_messages' => ['id'],
        'blogs'            => ['id'],
    ];
    foreach ($need as $table => $cols) {
        try {
            $have = array_column($conn->query("SHOW COLUMNS FROM `$table`")->fetch_all(MYSQLI_ASSOC), 'Field');
            $missing = array_diff($cols, $have);
            row(!$missing, "table $table", $missing ? 'missing columns: ' . implode(', ', $missing) . '  → run database/migrate.sql' : 'ok');
        } catch (mysqli_sql_exception $e) {
            row(false, "table $table", 'table not found  → run database/extra_tables.sql (or migrate.sql)');
        }
    }
    try {
        $n = (int)$conn->query('SELECT COUNT(*) FROM patients WHERE password NOT LIKE \'$2y$%\'')->fetch_row()[0];
        row($n === 0, 'Unhashed passwords in patients', $n === 0 ? 'none' : "$n found  → run: php database/migrate_passwords.php (or nobody can log in)");
    } catch (mysqli_sql_exception $e) { /* patients table problem already reported above */ }
}
echo '</table><p><strong>Delete check.php when you are done.</strong></p>';