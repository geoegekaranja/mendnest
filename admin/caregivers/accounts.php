<?php
/* Helpers shared by every page that creates or edits accounts or caregiver photos. */

const ACCOUNT_TABLE_NAMES = ['admins', 'caregivers', 'patients'];

if (!function_exists('email_in_use')) {
    /* True when $email already belongs to an account in ANY of the three tables, so one
       email can never be both an admin and a patient. Pass $exceptTable and $exceptId to
       ignore the account that is being edited. */
    function email_in_use(mysqli $conn, string $email, ?string $exceptTable = null, int $exceptId = 0): bool {
        foreach (ACCOUNT_TABLE_NAMES as $tbl) {
            $stmt = $conn->prepare("SELECT id FROM `$tbl` WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                if (!($tbl === $exceptTable && (int)$row['id'] === $exceptId)) {
                    $stmt->close();
                    return true;
                }
            }
            $stmt->close();
        }
        return false;
    }
}

if (!function_exists('caregiver_photo_url')) {
    /* Web address of a caregiver photo, or null when there is none. */
    function caregiver_photo_url(?string $photo): ?string {
        return ($photo && preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $photo))
            ? BASE_URL . '/uploads/caregivers/' . $photo
            : null;
    }
}

if (!function_exists('delete_caregiver_photo')) {
    function delete_caregiver_photo(?string $photo): void {
        if ($photo && preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $photo)) {
            $path = dirname(__DIR__) . '/uploads/caregivers/' . $photo;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}

if (!function_exists('save_uploaded_photo')) {
    /* Checks and stores an uploaded photo. Returns [file name or null, error message or ''].
       No file chosen is not an error: it returns [null, '']. */
    function save_uploaded_photo(array $file, string $uploadDir): array {
        $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($err === UPLOAD_ERR_NO_FILE) {
            return [null, ''];
        }
        if ($err !== UPLOAD_ERR_OK) {
            return [null, 'The photo could not be uploaded. Please try again.'];
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return [null, 'The photo must be 2 MB or smaller.'];
        }
        $info    = @getimagesize($file['tmp_name']);
        $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($allowed[$info[2]])) {
            return [null, 'The photo must be a JPG, PNG or WebP image.'];
        }
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            return [null, 'The upload folder could not be created.'];
        }
        $name = bin2hex(random_bytes(12)) . '.' . $allowed[$info[2]];   // random name, never the visitor's
        if (!move_uploaded_file($file['tmp_name'], rtrim($uploadDir, '/\\') . '/' . $name)) {
            return [null, 'The photo could not be saved.'];
        }
        return [$name, ''];
    }
}