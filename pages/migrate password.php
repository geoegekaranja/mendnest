<?php
/* ONE-TIME script: turns the plain-text passwords in `patients` into secure hashes.
   Run AFTER migrate.sql and BEFORE deploying the new login.php:
       php database/migrate_passwords.php
   Safe to run twice: already-hashed passwords are skipped. Delete this file afterwards. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Run from the command line only.'); }

require_once __DIR__ . '/connection.php';

// Refuse to run if the column is too short (the hash would be cut off and nobody could log in)
$col = $conn->query("SHOW COLUMNS FROM patients LIKE 'password'")->fetch_assoc();
if ($col && preg_match('/\((\d+)\)/', $col['Type'], $m) && (int)$m[1] < 60) {
    exit("The password column is only {$m[1]} characters. Run database/migrate.sql first.\n");
}

$rows = $conn->query("SELECT id, password FROM patients")->fetch_all(MYSQLI_ASSOC);
$upd  = $conn->prepare("UPDATE patients SET password = ? WHERE id = ?");
$done = 0;

foreach ($rows as $r) {
    $pw = (string)$r['password'];
    if ($pw === '' || strncmp($pw, '$2y$', 4) === 0) { continue; }   // empty or already hashed
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $upd->bind_param('si', $hash, $r['id']);
    $upd->execute();
    $done++;
}
echo "Hashed $done password(s).\n";