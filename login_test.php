<?php
/* Login test for the Mend Nest site.
   Open it in your browser:   http://localhost/mendnest/login_test.php
   Type the email (or caregiver ID) and the password you use to log in. It applies the SAME rules as
   login.php and shows which step passes or fails. It changes nothing and never shows a saved password.
   It only works on your own computer.  DELETE THIS FILE when you are done with it. */

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('This page can only be opened on your own computer.');
}
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/database/connection.php';

const TABLES       = ['admins' => 'admin', 'caregivers' => 'caregiver', 'patients' => 'patient'];
const HOME         = ['admin' => '/admin/admindashboard.php', 'caregiver' => '/caregiver/dashboard.php', 'patient' => '/patient/dashboard.php'];
const MARKER       = '#$';
const MARKER_ROLES = ['admin', 'caregiver'];

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$steps      = [];
$identifier = '';
$ran        = false;

function step(string $status, string $title, string $detail = ''): void {
    global $steps;
    $steps[] = [$status, $title, $detail];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ran        = true;
    $identifier = trim((string)($_POST['identifier'] ?? ''));
    $typed      = (string)($_POST['password'] ?? '');

    if ($identifier === '' || $typed === '') {
        step('bad', 'Input', 'Enter both the email (or caregiver ID) and the password.');
    } else {
        /* ---- 1. find the account: admins, then caregivers, then patients (like login.php) ---- */
        $row = null;
        $tbl = null;
        foreach (TABLES as $t => $role) {
            try {
                $isCg = ($t === 'caregivers');
                $sql  = "SELECT id, name, email, password, role, is_active, failed_attempts, locked_until,
                                (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked"
                      . ($isCg ? ", caregiver_code" : "")
                      . " FROM `$t` WHERE email = ?" . ($isCg ? " OR caregiver_code = ?" : "") . " LIMIT 1";
                $stmt = $conn->prepare($sql);
                if ($isCg) {
                    $stmt->bind_param('ss', $identifier, $identifier);
                } else {
                    $stmt->bind_param('s', $identifier);
                }
                $stmt->execute();
                $found = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } catch (Throwable $ex) {
                step('bad', "Reading table $t", $ex->getMessage() . '  Run roles_setup.sql.');
                continue;
            }
            if ($found) {
                $row = $found;
                $tbl = $t;
                break;
            }
        }

        if (!$row) {
            step('bad', 'Account found', 'No account matches "' . $identifier . '" in admins, caregivers or patients. '
                . 'Check the spelling, extra spaces, or that the account was saved in the right table.');
        } else {
            $expected = TABLES[$tbl];
            $where    = $tbl === 'caregivers' ? "caregiver_code = '" . ($row['caregiver_code'] ?? '') . "'" : "id = " . (int)$row['id'];
            step('ok', 'Account found', "Table `$tbl`, id " . (int)$row['id'] . ', name ' . $row['name']
                . ($tbl === 'caregivers' ? ', caregiver ID ' . ($row['caregiver_code'] ?: '(none yet)') : ''));

            /* ---- 2. role ---- */
            $roleValid = ($row['role'] === $expected);
            if ($roleValid) {
                step('ok', 'Role', "Saved role is '$expected', which belongs to this table. Login leads to " . HOME[$expected]);
            } else {
                step('bad', 'Role', "Saved role is '" . $row['role'] . "' but this table needs '$expected'. Fix: UPDATE `$tbl` SET role = '$expected' WHERE $where;");
            }

            /* ---- 3. active ---- */
            if ($row['is_active']) {
                step('ok', 'Account enabled', 'is_active = 1');
            } else {
                step('bad', 'Account enabled', "is_active = 0, so login is refused. Fix: UPDATE `$tbl` SET is_active = 1 WHERE $where;");
            }

            /* ---- 4. lock ---- */
            if ($row['is_locked']) {
                step('bad', 'Not locked', 'Locked until ' . $row['locked_until'] . ' after ' . (int)$row['failed_attempts']
                    . " wrong passwords. Fix: UPDATE `$tbl` SET failed_attempts = 0, locked_until = NULL WHERE $where;");
            } else {
                step('ok', 'Not locked', (int)$row['failed_attempts'] . ' recent wrong password(s)');
            }

            /* ---- 5. how the password is saved ---- */
            $stored   = $row['password'];
            $kind     = 'none';
            $plain    = '';
            if ($stored === null || $stored === '') {
                step('bad', 'Password saved', "No password is saved, so nobody can log in. Fix: UPDATE `$tbl` SET password = '" . MARKER . "ChooseAPassword' WHERE $where;");
            } elseif (str_starts_with($stored, MARKER)) {
                $plain = substr($stored, strlen(MARKER));
                if ($roleValid && in_array($row['role'], MARKER_ROLES, true)) {
                    $kind = 'marker';
                    $note = 'Saved in the #$ format. Log in with the password WITHOUT the #$ (' . strlen($plain) . ' characters).';
                    if ($plain !== trim($plain)) {
                        $note .= ' WARNING: it begins or ends with a space.';
                    }
                    step('ok', 'Password saved', $note);
                } else {
                    step('bad', 'Password saved', "The #$ format is not accepted for role '" . $row['role'] . "'.");
                }
            } elseif (str_starts_with($stored, '$2')) {
                $kind = 'hash';
                step('ok', 'Password saved', 'A real password hash (' . strlen($stored) . ' characters). Log in with the password that was hashed.');
            } else {
                step('bad', 'Password saved', 'Saved as PLAIN TEXT, which login cannot use. Fix: UPDATE `' . $tbl . "` SET password = CONCAT('" . MARKER . "', password) WHERE $where;  then log in with the same text.");
            }

            /* ---- 6. does the typed password match? ---- */
            if ($kind === 'marker') {
                $match = hash_equals($plain, $typed);
            } elseif ($kind === 'hash') {
                $match = password_verify($typed, $stored);
            } else {
                $match = false;
            }
            if ($kind !== 'none') {
                if ($match) {
                    step('ok', 'Typed password', 'Matches the saved password.');
                } else {
                    $hint = 'Does NOT match.';
                    if ($kind === 'marker') {
                        $hint .= ' The saved one is ' . strlen($plain) . ' characters, you typed ' . strlen($typed) . '.';
                        if (str_starts_with($typed, MARKER)) {
                            $hint .= ' You typed the #$ at the front: leave it out.';
                        }
                    }
                    $hint .= ' Check Caps Lock and capital letters.';
                    step('bad', 'Typed password', $hint);
                }
            }

            /* ---- verdict ---- */
            $allOk = true;
            foreach ($steps as $s) {
                if ($s[0] === 'bad') { $allOk = false; }
            }
            if ($allOk) {
                step('ok', 'RESULT', 'This login would WORK and open ' . HOME[$expected] . '.');
            } else {
                step('bad', 'RESULT', 'This login would be REFUSED. Fix the first PROBLEM above, then test again.');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Mend Nest login test</title>
<style>
body { font-family: Segoe UI, Arial, sans-serif; margin: 24px; max-width: 900px; color: #222; }
input { padding: 8px; width: 100%; box-sizing: border-box; margin: 4px 0 12px; }
button { padding: 9px 18px; background: #198754; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
table { border-collapse: collapse; width: 100%; margin-top: 18px; font-size: 14px; }
td { border-bottom: 1px solid #e5e5e5; padding: 7px 8px; vertical-align: top; }
.note { background: #fff8e1; border-left: 4px solid #b58100; padding: 8px 12px; }
</style>
</head>
<body>
<h1>Mend Nest login test</h1>
<p class="note">It changes nothing and never shows a saved password. Delete <strong>login_test.php</strong> when you are finished.</p>

<form method="POST" autocomplete="off">
    <label>Email or caregiver ID (for example CG-0001)</label>
    <input type="text" name="identifier" value="<?= e($identifier) ?>" required>
    <label>The password you type on the login page</label>
    <input type="password" name="password" required>
    <button type="submit">Test this login</button>
</form>

<?php if ($ran): ?>
<table>
    <?php foreach ($steps as [$status, $title, $detail]): ?>
        <tr>
            <td style="width:90px;font-weight:700;color:<?= $status === 'ok' ? '#198754' : '#dc3545' ?>">
                <?= $status === 'ok' ? 'OK' : 'PROBLEM' ?>
            </td>
            <td style="width:170px"><strong><?= e($title) ?></strong></td>
            <td><?= e($detail) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</body>
</html>
