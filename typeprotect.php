<?php

declare(strict_types=1);

/**
 * TypeProtect: password-protect any PHP page with a single line:
 *
 *     <?php require __DIR__ . '/typeprotect.php'; ?>
 *
 * How to set your password (in order of precedence):
 *   1. Set the TYPEPROTECT_PASSWORD_HASH environment variable, or
 *   2. Replace the $passwordHash value below.
 *
 * Generate a hash with:
 *   php -r 'echo password_hash("your-password", PASSWORD_DEFAULT), PHP_EOL;'
 *
 * The bundled default hashes the password "test", for the demo only.
 * Never ship the default to production.
 */

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

// A password_hash() digest (bcrypt/argon2), NOT a plain password.
$passwordHash = getenv('TYPEPROTECT_PASSWORD_HASH')
    ?: '$2y$12$mLc3.ZJzNmahHIZHnG5Yv.ap.qefnfOmnXiag0r9EnjrSMoRbNRQy'; // "test"

// Where to send the user after they sign out. Defaults to the current page.
$signOutRedirect = getenv('TYPEPROTECT_SIGNOUT_URL') ?: null;

// Brute-force throttling: after this many failed attempts, lock out for a while.
const TYPEPROTECT_MAX_ATTEMPTS = 5;
const TYPEPROTECT_LOCKOUT_SECONDS = 300;

// ---------------------------------------------------------------------------
// Session hardening
// ---------------------------------------------------------------------------

$isHttps =
    (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // not readable from JavaScript
        'secure'   => $isHttps, // only sent over HTTPS when available
        'samesite' => 'Strict', // mitigates CSRF on the session cookie
    ]);
    session_start();
}

$_SESSION['signedIn'] ??= false;
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['failedAttempts'] ??= 0;
$_SESSION['lockedUntil'] ??= 0;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** Redirect and stop, avoiding the "re-submit form?" prompt (POST/redirect/GET). */
$redirect = static function (string $url): never {
    header('Location: ' . $url, true, 303);
    exit;
};

// The path of the current request, without query string, used for self-redirects.
$selfUrl = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?') ?: '/';

$isWrongPass = false;
$now = time();
$lockRemaining = max(0, (int) $_SESSION['lockedUntil'] - $now);

// ---------------------------------------------------------------------------
// Sign out
// ---------------------------------------------------------------------------

if (isset($_GET['signout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => $now - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);
    }
    session_destroy();
    $redirect($signOutRedirect ?? $selfUrl);
}

// ---------------------------------------------------------------------------
// Sign in
// ---------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfOk = hash_equals((string) $_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''));

    if ($lockRemaining > 0) {
        // Locked out, ignore the attempt.
        $isWrongPass = true;
    } elseif (!$csrfOk) {
        // Bad or missing CSRF token, reject without touching the counter.
        $isWrongPass = true;
    } elseif (password_verify((string) ($_POST['password'] ?? ''), $passwordHash)) {
        // Success: reset counters and rotate the session id (anti-fixation).
        session_regenerate_id(true);
        $_SESSION['signedIn'] = true;
        $_SESSION['failedAttempts'] = 0;
        $_SESSION['lockedUntil'] = 0;
        $redirect($selfUrl);
    } else {
        $_SESSION['failedAttempts']++;
        if ($_SESSION['failedAttempts'] >= TYPEPROTECT_MAX_ATTEMPTS) {
            $_SESSION['lockedUntil'] = $now + TYPEPROTECT_LOCKOUT_SECONDS;
            $_SESSION['failedAttempts'] = 0;
        }
        $isWrongPass = true;
        $lockRemaining = max(0, (int) $_SESSION['lockedUntil'] - $now);
    }
}

// Already signed in? Let the protected page render.
if ($_SESSION['signedIn'] === true) {
    return;
}

// Otherwise show the sign-in form and stop before the protected content.
$csrfToken = (string) $_SESSION['csrf'];
$isDefaultPassword = password_verify('test', $passwordHash);

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Password Protected Page</title>
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style>
			*, *::before, *::after { box-sizing: border-box; }

			html {
				height: 100%;
				background: #f5f5f5;
			}

			body {
				margin: 0;
				font: normal 16px/26px Helvetica, Arial, sans-serif;
				background: #f5f5f5;
				padding-top: 80px;
			}

			h1 {
				color: #ff5544;
				font: bold 18px/28px Helvetica, Arial, sans-serif;
				padding-bottom: 15px;
				border-bottom: 1px solid #eee;
				margin: 0 0 10px 0;
			}

			#box-signIn {
				width: 300px;
				max-width: 90vw;
				background: #fff;
				padding: 30px 50px 50px 50px;
				border: 1px solid #eee;
				margin: 0 auto;
			}

			div.error {
				color: #ff5544;
				font: normal 14px/24px Helvetica, Arial, sans-serif;
				margin-top: 20px;
			}

			form#signIn label {
				color: #aaa;
				font: normal 12px/22px Helvetica, Arial, sans-serif;
				text-transform: uppercase;
				letter-spacing: 2px;
				display: block;
				margin: 25px 0 15px 0;
			}

			form#signIn input[type="password"] {
				color: #777;
				border: 1px solid #eee;
				padding: 20px;
				width: 100%;
				outline: none;
				display: block;
			}

			form#signIn input[type="password"]:focus {
				border: 1px solid #ff5544;
			}

			form#signIn input.submit {
				color: #fff;
				background: #ff5544;
				font: bold 15px/25px Helvetica, Arial, sans-serif;
				text-transform: uppercase;
				letter-spacing: 2px;
				margin-top: 40px;
				width: 100%;
				padding: 25px 0;
				border: none;
				cursor: pointer;
				border-radius: 5px;
			}

			form#signIn input.submit:hover {
				color: #ff5544;
				background: #000;
			}

			form#signIn input.submit:disabled {
				background: #ccc;
				color: #fff;
				cursor: not-allowed;
			}

			@media only screen and (max-width: 479px) {
				#box-signIn {
					width: 240px;
					padding: 20px 30px 30px 30px;
				}
			}
		</style>
	</head>

	<body>
		<div id="box-signIn">
			<h1>Protected Page</h1>

			<?php if ($lockRemaining > 0): ?>
				<div class="error">
					Too many attempts. Try again in
					<?= (int) ceil($lockRemaining / 60) ?> minute(s).
				</div>
			<?php elseif ($isWrongPass): ?>
				<div class="error">
					Wrong password.<?php if ($isDefaultPassword): ?><br>Default password is "test".<?php endif; ?>
				</div>
			<?php endif; ?>

			<form id="signIn" method="post" autocomplete="off">
				<input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">

				<label for="password">Password</label>
				<input type="password" id="password" name="password"
					autocomplete="current-password" autofocus
					<?= $lockRemaining > 0 ? 'disabled' : '' ?>>

				<input type="submit" name="submit" class="submit" value="Sign In"
					<?= $lockRemaining > 0 ? 'disabled' : '' ?>>
			</form>
		</div>
	</body>
</html>
<?php
exit;
