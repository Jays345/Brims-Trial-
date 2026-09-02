<?php
// upload_csv.php
header('Content-Type: application/json');
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!isset($_FILES['file'])) { echo json_encode(['error'=>'no file']); exit; }
$f = $_FILES['file'];
$ext = pathinfo($f['name'], PATHINFO_EXTENSION);
if (strtolower($ext) !== 'csv') { echo json_encode(['error'=>'not csv']); exit; }

$fn = uniqid('csv_') . '.csv';
$dest = "$uploadDir/$fn";
if (!move_uploaded_file($f['tmp_name'], $dest)) { echo json_encode(['error'=>'upload failed']); exit; }

// attempt simple analysis: parse first column numeric series
$fp = fopen($dest,'r');
$values = [];
$header = fgetcsv($fp);
while (($row = fgetcsv($fp)) !== false) {
  foreach ($row as $col) {
    $val = trim($col);
    if ($val !== '' && is_numeric($val)) { $values[] = (float)$val; }
  }
}
fclose($fp);

$summary = [];
if (count($values)>0) {
  $count = count($values);
  $sum = array_sum($values);
  $mean = $sum/$count;
  $min = min($values);
  $max = max($values);
  $summary = ['count'=>$count,'sum'=>$sum,'mean'=>$mean,'min'=>$min,'max'=>$max];
}

// store filename to DB (optional)
try {
  $cfg = ['host'=>'127.0.0.1','db'=>'chatbot_db','user'=>'root','pass'=>'','charset'=>'utf8mb4'];
  $dsn = "mysql:host={$cfg['host']};dbname={$cfg['db']};charset={$cfg['charset']}";
  $pdo = new PDO($dsn,$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $stmt = $pdo->prepare("INSERT INTO uploaded_files (filename) VALUES (:f)");
  $stmt->execute(['f'=>$fn]);
} catch (Exception $e) {
  // ignore DB errors, return analysis anyway
}

echo json_encode(['ok'=>true,'summary'=>$summary,'chart'=>$values]);
