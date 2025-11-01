<?php
include 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user'])) { echo json_encode(['success'=>false,'error'=>'Unauthenticated']); exit; }
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description'] ?? '');
    $priority = in_array($_POST['priority'],['low','medium','high'])?$_POST['priority']:'medium';
    $due = $_POST['due_date'] ?: null;
    $assignee = intval($_POST['assignee_id'] ?: 0) ?: null;
    $project_id = intval($_POST['project_id']);
    $creator = intval($_SESSION['user']['id']);
    $stmt = $conn->prepare("INSERT INTO tasks (project_id,title,description,priority,due_date,assignee_id,created_by) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("issssis",$project_id,$title,$desc,$priority,$due,$assignee,$creator);
    if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$conn->insert_id]); else echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}

if ($action === 'fetch') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT t.*, u.name as assignee FROM tasks t LEFT JOIN users u ON t.assignee_id=u.id WHERE t.id=?");
    $stmt->bind_param("i",$id); $stmt->execute(); $res = $stmt->get_result();
    $task = $res->fetch_assoc();
    if ($task) echo json_encode(['success'=>true,'task'=>$task]); else echo json_encode(['success'=>false,'error'=>'Not found']);
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description'] ?? '');
    $priority = in_array($_POST['priority'],['low','medium','high'])?$_POST['priority']:'medium';
    $due = $_POST['due_date'] ?: null;
    $assignee = intval($_POST['assignee_id'] ?: 0) ?: null;
    $stmt = $conn->prepare("UPDATE tasks SET title=?,description=?,priority=?,due_date=?,assignee_id=? WHERE id=?");
    $stmt->bind_param("ssssii",$title,$desc,$priority,$due,$assignee,$id);
    if ($stmt->execute()) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=?"); $stmt->bind_param("i",$id);
    if ($stmt->execute()) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}

if ($action === 'change_status') {
    $id = intval($_POST['id']);
    $status = in_array($_POST['status'],['todo','inprogress','done'])?$_POST['status']:'todo';
    $stmt = $conn->prepare("UPDATE tasks SET status=? WHERE id=?"); $stmt->bind_param("si",$status,$id);
    if ($stmt->execute()) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Invalid action']);
