<?php
declare(strict_types=1);

/**
 * ITCS333 Project starter — m1_auth handler (module under construction).
 * Implement the endpoints in contracts/ for your module.
 * The routing context is in $GLOBALS['__route'] ('area', 'rest', 'uri').
 * Use db() from src/db.php and the helpers in src/http.php.
 */

$rest = $GLOBALS['__route']['rest'] ?? '';

$method = http_method();
$body   = json_body();
$pdo    = db();

if ($rest === '/register' && $method === 'POST') {
$username = trim((string) ($body['username'] ?? ''));
$password = (string) ($body['password'] ?? '');
$isAdmin = !empty($body['is_admin']) ? 1 : 0;
$msg = [];
if ($username === ''){
$msg['username'] = 'required';
}
if ($password === ''){
$msg['password'] = 'required';
}
if($msg){
send_error(422, 'VALIDATION', ['fields' => $msg]);
}
$stmt = $pdo->prepare("SELECT username FROM users WHERE username = ?");
$stmt->execute([$username]);
$find= $stmt->fetch();
if ($find) {
    send_error(409, 'USERNAME_TAKEN');
}
$id = db_insert_and_get_id($pdo, 'users', [
'username' => $username,
'password_hash' => password_hash($password, PASSWORD_DEFAULT),
'is_admin' => $isAdmin
]);
send_json(201, ['id' => $id, 'username' => $username, 'is_admin' => $isAdmin]);
} 

else if ($rest === '/login' && $method === 'POST') {
$username = trim((string) ($body['username'] ?? ''));
$password = (string) ($body['password'] ?? '');
$stmt = $pdo->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = ?");
$stmt->execute([$username]);
$find= $stmt->fetch();
if(!$find || !password_verify($password, $find['password_hash'])){
send_error(401, 'INVALID_CREDENTIALS');
}
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['username'] = $user['username'];
send_json(200, ['id' => (int) $find['id'], 'username' => $find['username'], 'is_admin' => (int) $find['is_admin'], 'token' => session_id()]);
} 

else if ($rest === '/logout' && $method === 'POST') {
$_SESSION = [];
session_destroy();
send_json(200, ['ok' => true]);  
} 

else if ($rest === '/me' && $method === 'GET') {
$UserId   = $_SESSION['user_id'] ?? null;
$Username = $_SESSION['username'] ?? null;
if(!$UserId){
send_error(401, 'AUTH_REQUIRED');
}
$stmt = $pdo->prepare("SELECT id, username, is_admin FROM users WHERE id = ?");
$stmt->execute([(int) $UserId]);
$user = $stmt->fetch();
if (!$user) {
send_error(401, 'AUTH_REQUIRED');
}
send_json(200, ['id' => (int) $user['id'], 'username' => $user['username'], 'is_admin' => (int) $user['is_admin']]);
} 

else {
send_error(404, 'NOT_FOUND');
 }

send_error(501, 'NOT_IMPLEMENTED', ['module' => 'm1_auth', 'rest' => $rest]);