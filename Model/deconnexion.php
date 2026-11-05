<?php
// Ouvre la session pour pouvoir la vider
session_start();
$_SESSION = [];

// Supprime le cookie de session dans le navigateur
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

// Détruit la session, puis renvoie vers la page de connexion
session_destroy();
header('Location: connexion.php');
exit;