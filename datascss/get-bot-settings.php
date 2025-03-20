<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connection.php';

$sql = "SELECT bs.id, b.bot_name, g.group_name 
        FROM bot_settings bs
        JOIN bots b ON bs.bot_id = b.id
        JOIN groups g ON bs.group_id = g.id";
$result = $conn->query($sql);

$settings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $settings]);
} else {
    echo json_encode(['status' => 'success', 'data' => []]);
}

$conn->close();
?>