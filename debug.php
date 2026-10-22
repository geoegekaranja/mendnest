<?php
require_once __DIR__ . '/includes/auth.php';
echo '<pre>';
echo 'BASE_URL:  ' . BASE_URL . "\n";
echo 'LOGIN_URL: ' . LOGIN_URL . "\n";
echo 'ROLE_HOME: '; print_r(ROLE_HOME);
echo 'Folder on disk: ' . __DIR__ . "\n";
echo 'URL you opened: ' . $_SERVER['REQUEST_URI'] . "\n";