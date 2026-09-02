<?php
// save_message.php
header('Content-Type: application/json');
$cfg = ['host'=>'127.0.0.1','db'=>'chatbot_db','user'=>'root','pass'=>'','charset'=>'utf8mb4'];

$input = json_decode(file_get_contents('php://input'), true);
$sender = $input['sender'] ?? null;
$message = $input['message'] ?? null;
if (!$sender || !$message) { echo json_encode(['ok'=>false,'error'=>'missing']); exit; }

$dsn = "mysql:host={$cfg['host']};dbname={$cfg['db']};charset={$cfg['charset']}";
try {
  $pdo = new PDO($dsn,$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $stmt = $pdo->prepare("INSERT INTO chat_messages (sender,message) VALUES (:sender,:message)");
  $stmt->execute(['sender'=>$sender,'message'=>$message]);
  echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
} catch (PDOException $e) {
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
