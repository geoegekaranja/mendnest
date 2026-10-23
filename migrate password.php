<?php
/* ONE-TIME script: converts every admin password stored in the "#$Password" format
   into a real bcrypt hash, so the admins do not have to log in first.

     before:  #$YourNewPassword
     after:   $2y$10$....................................................   (60 characters)

   - Only runs from your own computer (localhost).
   - Only touches rows where role = 'admin' and the password starts with "#$".
   - Safe to run twice: rows that are already hashed are left alone.
   - Never prints any password.
   - DELETE THIS FILE as soon as you have run it. */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('This script can only be run on your own computer.');
}

require_once __DIR__ . '/database/connection.php';
header('Content-Type: text/plain; charset=utf-8');

const ADMIN_TEMP_PREFIX = '#$';   // must match login.php

try {
    $rows = $conn->query(
        "SELECT id, email, role, password FROM patients WHERE LEFT(password, 2) = '#$'"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    exit('Could not read the patients table: ' . $ex->getMessage() . "\n");
}

if (!$rows) {
    exit("Nothing to migrate: no password starting with #\$ was found.\nDELETE migrate_password.php now.\n");
}

$up        = $conn->prepare("UPDATE patients SET password = ? WHERE id = ?");
$converted = 0;
$skipped   = 0;

foreach ($rows as $r) {
    $plain = substr((string)$r['password'], strlen(ADMIN_TEMP_PREFIX));

    if ($r['role'] !== 'admin') {
        // The #$ format is for admins only, and login.php rejects it for everyone else
        echo "SKIPPED  {$r['email']}  (role is '{$r['role']}', not admin; fix the role or the password first)\n";
        $skipped++;
        continue;
    }
    if ($plain === '') {
        echo "SKIPPED  {$r['email']}  (nothing after the #\$ marker)\n";
        $skipped++;
        continue;
    }

    $hash = password_hash($plain, PASSWORD_DEFAULT);
    $id   = (int)$r['id'];
    $up->bind_param('si', $hash, $id);
    $up->execute();
    echo "HASHED   {$r['email']}\n";
    $converted++;
}
$up->close();

echo "\nConverted: $converted   Skipped: $skipped\n";
echo "Each converted admin still logs in with the same password, WITHOUT the #\$ in front.\n";
echo "Now DELETE migrate_password.php from your project folder.\n";