<?php
header('Content-Type: application/json');
$cfg = ['host'=>'127.0.0.1','db'=>'chatbot_db','user'=>'root','pass'=>'','charset'=>'utf8mb4'];

$dsn = "mysql:host={$cfg['host']};dbname={$cfg['db']};charset={$cfg['charset']}";
try {
  $pdo = new PDO($dsn,$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
  $stmt = $pdo->query("SELECT sender, message, created_at FROM chat_messages ORDER BY id DESC LIMIT 50");
  $rows = $stmt->fetchAll();
  echo json_encode($rows);
} catch (PDOException $e) {
  echo json_encode([]);
}
