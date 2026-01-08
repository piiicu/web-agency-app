<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config/database.php';

$email = 'admin@local.test';
$pass  = 'admin1234';

$u = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$u->execute([$email]);
$user = $u->fetch(PDO::FETCH_ASSOC);

if (!$user) { exit("User not found\n"); }

echo "Found user id={$user['id']}\n";

$pwdCol = null;
foreach (['password','password_hash','pass','passwd'] as $c) {
  if (array_key_exists($c, $user)) { $pwdCol = $c; break; }
}
if (!$pwdCol) { exit("No password column found in row\n"); }

echo "Password column: $pwdCol\n";
echo "Hash value: " . substr((string)$user[$pwdCol], 0, 60) . "...\n";

if (password_verify($pass, (string)$user[$pwdCol])) {
  echo "password_verify: OK\n";
} else {
  echo "password_verify: FAIL\n";
}
