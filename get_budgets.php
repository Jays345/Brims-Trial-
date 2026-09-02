<?php
include 'db.php';
header('Content-Type: application/json');

$stmt = $pdo->prepare("
  SELECT b.*, 
  IFNULL(SUM(e.amount),0) as total_expenses,
  IFNULL(SUM(s.amount),0) as total_sales
  FROM budgets b
  LEFT JOIN expenses e ON b.budget_id=e.budget_id
  LEFT JOIN sales s ON b.budget_id=s.budget_id
  GROUP BY b.budget_id
  ORDER BY b.created_at DESC
");
$stmt->execute();
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['budgets'=>$budgets]);
?>
