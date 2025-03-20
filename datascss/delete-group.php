<?php
header('Content-Type: application/json; charset=UTF-8');
ob_start();
require_once __DIR__ . '/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$group_id = isset($data['group_id']) ? (int)$data['group_id'] : null;

if (!$group_id) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid group ID']);
    exit;
}

$conn->begin_transaction();

try {
    $sql_chat = "DELETE FROM chat_ids WHERE group_id = ?";
    $stmt_chat = $conn->prepare($sql_chat);
    if (!$stmt_chat) throw new Exception("Failed to prepare chat_ids statement: " . $conn->error);
    $stmt_chat->bind_param("i", $group_id);
    $stmt_chat->execute();
    $stmt_chat->close();

    $sql_group = "DELETE FROM groups WHERE id = ?";
    $stmt_group = $conn->prepare($sql_group);
    if (!$stmt_group) throw new Exception("Failed to prepare groups statement: " . $conn->error);
    $stmt_group->bind_param("i", $group_id);
    $stmt_group->execute();

    if ($stmt_group->affected_rows > 0) {
        $conn->commit();
        $response = ['status' => 'success', 'message' => 'Group deleted successfully'];
    } else {
        $conn->rollback();
        $response = ['status' => 'error', 'message' => 'Group not found'];
    }
    $stmt_group->close();
} catch (Exception $e) {
    $conn->rollback();
    $response = ['status' => 'error', 'message' => 'Failed to delete group: ' . $e->getMessage()];
}

$conn->close();
ob_end_clean();
echo json_encode($response);