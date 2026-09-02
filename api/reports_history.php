<?php
header("Content-Type: application/json");
require_once "db_connect.php";

$data = [];
$sql = "SELECT * FROM reports ORDER BY generated_at DESC";
$res = $conn->query($sql);

if($res){
    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }
}

echo json_encode($data);
?>
