<?php
header("Content-Type: application/json");
require_once "../db_connect.php"; 

$type = $_POST['type'] ?? null;
$details = $_POST['details'] ?? null;

if(!$type || !$details){
    echo json_encode(['status'=>'error','message'=>'Missing type or details']);
    exit;
}

// Decode JSON
$dataArray = json_decode($details, true);
if(!$dataArray || !is_array($dataArray)){
    echo json_encode(['status'=>'error','message'=>'Invalid JSON format']);
    exit;
}

try {
    foreach($dataArray as $row){
        switch($type){
            case 'categories':
                $stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?,?)");
                $stmt->bind_param("ss", $row['category_name'], $row['description']);
                $stmt->execute();
                break;

            case 'suppliers':
                $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, created_at) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("ssssss", $row['supplier_name'], $row['contact_person'], $row['phone'], $row['email'], $row['address'], $row['created_at']);
                $stmt->execute();
                break;

            case 'products':
                $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, supplier_id, cost_price, selling_price, stock_quantity, created_at) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param("siidids", $row['product_name'], $row['category_id'], $row['supplier_id'], $row['cost_price'], $row['selling_price'], $row['stock_quantity'], $row['created_at']);
                $stmt->execute();
                break;

            case 'orders':
                $stmt = $conn->prepare("INSERT INTO orders (supplier_id, customer_name, product_name, quantity, total_price, status, order_date) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param("issidss", $row['supplier_id'], $row['customer_name'], $row['product_name'], $row['quantity'], $row['total_price'], $row['status'], $row['order_date']);
                $stmt->execute();
                break;

            case 'expenses':
                $stmt = $conn->prepare("INSERT INTO expenses (expense_name, amount, expense_date, recorded_by) VALUES (?,?,?,?)");
                $stmt->bind_param("sdsi", $row['expense_name'], $row['amount'], $row['expense_date'], $row['recorded_by']);
                $stmt->execute();
                break;

            case 'budget_details':
                $stmt = $conn->prepare("INSERT INTO budget_details (category, approved_budget, proposed_budget, difference_amount, difference_percent, created_at) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("sdddss", $row['category'], $row['approved_budget'], $row['proposed_budget'], $row['difference_amount'], $row['difference_percent'], $row['created_at']);
                $stmt->execute();
                break;

            default:
                echo json_encode(['status'=>'error','message'=>'Unknown type']);
                exit;
        }
    }

    echo json_encode(['status'=>'success','message'=>'Data inserted successfully']);

} catch(Exception $e){
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
?>
