<?php
include 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user'])) { echo json_encode(['success'=>false,'error'=>'Unauthenticated']); exit; }
$action = $_REQUEST['action'] ?? '';

if ($action === 'list') {
    $task_id = intval($_REQUEST['task_id']);
    $stmt = $conn->prepare("SELECT c.*, u.name as user FROM comments c JOIN users u ON c.user_id=u.id WHERE c.task_id=? ORDER BY c.created_at DESC");
    $stmt->bind_param("i",$task_id); $stmt->execute(); $res = $stmt->get_result();
    $list = [];
    while($r=$res->fetch_assoc()) $list[] = $r;
    echo json_encode(['success'=>true,'comments'=>$list]);
    exit;
}

if ($action === 'add') {
    $task_id = intval($_POST['task_id']);
    $body = $conn->real_escape_string(trim($_POST['body']));
    if ($body=='') { echo json_encode(['success'=>false,'error'=>'Empty']); exit; }
    $uid = intval($_SESSION['user']['id']);
    $stmt = $conn->prepare("INSERT INTO comments (task_id,user_id,body) VALUES (?,?,?)");
    $stmt->bind_param("iis",$task_id,$uid,$body);
    if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$conn->insert_id]); else echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}
echo json_encode(['success'=>false,'error'=>'Invalid action']);
?>