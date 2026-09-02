<?php
header("Content-Type: application/json");
require_once "../db_connect.php";

$type = $_POST['type'] ?? null;
$details = $_POST['details'] ?? null;

if (!$type || !$details) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$data = json_decode($details, true);
if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// Ensure data is always an array
if (!is_array($data)) $data = [$data];

try {
    switch ($type) {
        case 'orders':
            $stmt = $conn->prepare(
                "INSERT INTO orders (supplier_id, customer_name, product_name, quantity, total_price, status, order_date) 
                VALUES (?,?,?,?,?,?,?)"
            );
            foreach ($data as $item) {
                $stmt->bind_param(
                    "issidss",
                    $item['supplier_id'],
                    $item['customer_name'],
                    $item['product_name'],
                    $item['quantity'],
                    $item['total_price'],
                    $item['status'],
                    $item['order_date']
                );
                $stmt->execute();
            }
            break;

        case 'suppliers':
            $stmt = $conn->prepare(
                "INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, created_at) 
                VALUES (?,?,?,?,?,?)"
            );
            foreach ($data as $item) {
                $stmt->bind_param(
                    "ssssss",
                    $item['supplier_name'],
                    $item['contact_person'],
                    $item['phone'],
                    $item['email'],
                    $item['address'],
                    $item['created_at']
                );
                $stmt->execute();
            }
            break;

        case 'products':
            $stmt = $conn->prepare(
                "INSERT INTO products (product_name, category_id, supplier_id, cost_price, selling_price, stock_quantity, created_at) 
                VALUES (?,?,?,?,?,?,?)"
            );
            foreach ($data as $item) {
                $stmt->bind_param(
                    "siidids",
                    $item['product_name'],
                    $item['category_id'],
                    $item['supplier_id'],
                    $item['cost_price'],
                    $item['selling_price'],
                    $item['stock_quantity'],
                    $item['created_at']
                );
                $stmt->execute();
            }
            break;

        case 'expenses':
            $stmt = $conn->prepare(
                "INSERT INTO expenses (expense_name, amount, expense_date, recorded_by) 
                VALUES (?,?,?,?)"
            );
            foreach ($data as $item) {
                $stmt->bind_param(
                    "sdsi",
                    $item['expense_name'],
                    $item['amount'],
                    $item['expense_date'],
                    $item['recorded_by']
                );
                $stmt->execute();
            }
            break;

        case 'budget_details':
            $stmt = $conn->prepare(
                "INSERT INTO budget_details (category, approved_budget, proposed_budget, difference_amount, difference_percent, created_at) 
                VALUES (?,?,?,?,?,?)"
            );
            foreach ($data as $item) {
                $stmt->bind_param(
                    "sdddss",
                    $item['category'],
                    $item['approved_budget'],
                    $item['proposed_budget'],
                    $item['difference_amount'],
                    $item['difference_percent'],
                    $item['created_at']
                );
                $stmt->execute();
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
            exit;
    }

    echo json_encode(['status' => 'success', 'message' => 'Data stored successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
