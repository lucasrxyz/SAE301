<?php
session_start();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametres = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parametres['path'],
        'domain' => $parametres['domain'],
        'secure' => $parametres['secure'],
        'httponly' => $parametres['httponly'],
        'samesite' => $parametres['samesite'] ?? 'Lax',
    ]);
}

session_destroy();
header('Location: connexion.php');
exit;