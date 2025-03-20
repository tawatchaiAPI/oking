<?php
header('Content-Type: application/json; charset=UTF-8');
ob_start();
require_once __DIR__ . '/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$bot_id = isset($data['bot_id']) ? (int)$data['bot_id'] : null;

if (!$bot_id) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid bot ID']);
    exit;
}

try {
    $sql = "DELETE FROM bots WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Failed to prepare statement: " . $conn->error);
    $stmt->bind_param("i", $bot_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $response = ['status' => 'success', 'message' => 'Bot deleted successfully'];
    } else {
        $response = ['status' => 'error', 'message' => 'Bot not found'];
    }
    $stmt->close();
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => 'Failed to delete bot: ' . $e->getMessage()];
}

$conn->close();
ob_end_clean();
echo json_encode($response);
?>