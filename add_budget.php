<?php
include 'db.php';
header('Content-Type: application/json');
$data=json_decode(file_get_contents('php://input'),true);
$stmt=$pdo->prepare("INSERT INTO budgets (budget_name, amount, start_date, end_date) VALUES (?,?,?,?)");
$result=$stmt->execute([$data['budget_name'],$data['amount'],$data['start_date'],$data['end_date']]);
echo json_encode(['success'=>$result]);
?>
