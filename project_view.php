<?php
include 'db.php';
if (!isset($_SESSION['user'])) header("Location: login.php");
$project_id = intval($_GET['id'] ?? 0);
$project = null;
if ($project_id) {
    $stmt = $conn->prepare("SELECT p.*, u.name as owner FROM projects p LEFT JOIN users u ON p.owner_id=u.id WHERE p.id=?");
    $stmt->bind_param("i",$project_id); $stmt->execute(); $res=$stmt->get_result(); $project = $res->fetch_assoc();
    if (!$project) { echo "Project not found"; exit; }
} else { header("Location: projects.php"); exit; }

function fetchTasksByStatus($conn,$project_id,$status){
    $st = $conn->prepare("SELECT t.*, u.name as assignee FROM tasks t LEFT JOIN users u ON t.assignee_id=u.id WHERE t.project_id=? AND t.status=? ORDER BY t.created_at DESC");
    $st->bind_param("is",$project_id,$status); $st->execute(); return $st->get_result();
}
$todo = fetchTasksByStatus($conn,$project_id,'todo');
$inprog = fetchTasksByStatus($conn,$project_id,'inprogress');
$done = fetchTasksByStatus($conn,$project_id,'done');
$users = $conn->query("SELECT id,name FROM users ORDER BY name");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($project['title']); ?></title>
<style>
body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  background: #eef1f6;
  color: #333;
}
header {
  background: linear-gradient(90deg, #007bff, #6610f2);
  color: white;
  padding: 16px 24px;
  font-size: 20px;
  font-weight: 600;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
nav {
  background: #1f1f1f;
  position: fixed;
  left: 0;
  top: 0;
  width: 220px;
  height: 100vh;
  padding-top: 80px;
  box-shadow: 2px 0 8px rgba(0,0,0,0.15);
}
nav a {
  display: block;
  color: #bbb;
  text-decoration: none;
  padding: 12px 20px;
  transition: 0.3s;
}
nav a:hover {
  background: #007bff;
  color: #fff;
}
.main {
  margin-left: 240px;
  padding: 30px;
}
h2 {
  font-size: 24px;
  margin-bottom: 20px;
}
.btn {
  background: linear-gradient(90deg,#007bff,#6610f2);
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.3s;
}
.btn:hover { opacity: 0.85; }

.board {
  display: flex;
  gap: 20px;
}
.column {
  flex: 1;
  background: #fff;
  border-radius: 12px;
  padding: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  transition: 0.3s;
}
.column:hover {
  transform: translateY(-3px);
}
.column h3 {
  text-align: center;
  color: #007bff;
  margin-bottom: 15px;
  border-bottom: 2px solid #eee;
  padding-bottom: 8px;
}
.task {
  background: #f8f9fc;
  padding: 10px 12px;
  border-radius: 8px;
  margin-bottom: 10px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  cursor: grab;
  transition: 0.2s;
}
.task:hover {
  background: #e9f1ff;
  transform: scale(1.02);
}
.task.dragging { opacity: 0.5; }

.modal {
  position: fixed;
  left: 0; top: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 100;
}
.modal .box {
  background: #fff;
  padding: 24px;
  border-radius: 12px;
  width: 520px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.2);
}
.form-row { margin: 10px 0; }
input, textarea, select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-family: inherit;
}
.comment {
  background: #f3f5f9;
  padding: 8px;
  border-radius: 8px;
  margin-bottom: 6px;
}
.comment strong { color: #007bff; }
</style>
</head>
<body>
<header><?php echo htmlspecialchars($project['title']); ?> — <small><?php echo htmlspecialchars($project['owner']); ?></small></header>

<nav>
  <a href="index.php">🏠 Dashboard</a>
  <a href="projects.php">📁 Projects</a>
  <a href="analytics.php">📊 Analytics</a>
</nav>

<div class="main">
  <div style="display:flex;justify-content:space-between;align-items:center">
    
    <button class="btn" onclick="openCreate()">+ Add Task</button>
  </div>

  <div class="board">
    <div class="column" data-status="todo" ondragover="evtAllow(event)" ondrop="onDrop(event,'todo')">
      <h3>📝 To Do</h3>
      <div id="col-todo">
        <?php while($t = $todo->fetch_assoc()): ?>
          <div class="task" draggable="true" data-id="<?php echo $t['id'];?>" ondragstart="onDragStart(event)" onclick="openTask(<?php echo $t['id'];?>)">
            <strong><?php echo htmlspecialchars($t['title']); ?></strong><br>
            <small><?php echo htmlspecialchars($t['assignee'] ?? 'Unassigned'); ?></small>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="column" data-status="inprogress" ondragover="evtAllow(event)" ondrop="onDrop(event,'inprogress')">
      <h3>🚧 In Progress</h3>
      <div id="col-inprogress">
        <?php while($t = $inprog->fetch_assoc()): ?>
          <div class="task" draggable="true" data-id="<?php echo $t['id'];?>" ondragstart="onDragStart(event)" onclick="openTask(<?php echo $t['id'];?>)">
            <strong><?php echo htmlspecialchars($t['title']); ?></strong><br>
            <small><?php echo htmlspecialchars($t['assignee'] ?? 'Unassigned'); ?></small>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="column" data-status="done" ondragover="evtAllow(event)" ondrop="onDrop(event,'done')">
      <h3>✅ Done</h3>
      <div id="col-done">
        <?php while($t = $done->fetch_assoc()): ?>
          <div class="task" draggable="true" data-id="<?php echo $t['id'];?>" ondragstart="onDragStart(event)" onclick="openTask(<?php echo $t['id'];?>)">
            <strong><?php echo htmlspecialchars($t['title']); ?></strong><br>
            <small><?php echo htmlspecialchars($t['assignee'] ?? 'Unassigned'); ?></small>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="modal" class="modal">
  <div class="box">
    <h3 id="modalTitle">New Task</h3>
    <form id="taskForm" onsubmit="return submitTaskForm()">
      <input type="hidden" name="id" id="task_id">
      <div class="form-row"><input id="title" name="title" placeholder="Title" required></div>
      <div class="form-row"><textarea id="desc" name="description" placeholder="Description"></textarea></div>
      <div class="form-row">
        <select id="priority" name="priority">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
        </select>
        <input type="date" id="due" name="due_date">
        <select id="assignee" name="assignee_id">
          <option value="">Unassigned</option>
          <?php while($u=$users->fetch_assoc()): ?>
            <option value="<?php echo $u['id'];?>"><?php echo htmlspecialchars($u['name']);?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div style="margin-top:8px">
        <button class="btn" type="submit">Save</button>
        <button type="button" onclick="closeModal()">Cancel</button>
        <button type="button" id="deleteBtn" style="background:#dc3545;color:#fff;border:0;padding:8px;border-radius:6px;display:none" onclick="deleteTask()">Delete</button>
      </div>
    </form>
    <hr>
    <div id="commentsSection" style="max-height:220px;overflow:auto"></div>
    <div style="margin-top:8px;display:flex;gap:6px">
      <input id="commentText" placeholder="Write comment..." style="flex:1;padding:8px;border-radius:6px;border:1px solid #ccc">
      <button onclick="postComment()" class="btn">Send</button>
    </div>
  </div>
</div>

<script>
let draggedId = null;
function onDragStart(e){ draggedId = e.target.dataset.id; e.dataTransfer.setData('text/plain', draggedId); }
function evtAllow(e){ e.preventDefault(); }
function onDrop(e,status){ e.preventDefault(); const id = e.dataTransfer.getData('text'); changeStatus(id,status); }

function ajax(url,data,cb){
  const x = new XMLHttpRequest();
  x.open('POST',url);
  x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  x.onload = ()=> cb && cb(JSON.parse(x.responseText || '{}'));
  x.send(Object.keys(data).map(k=>encodeURIComponent(k)+'='+encodeURIComponent(data[k])).join('&'));
}
function changeStatus(id,status){
  ajax('tasks_api.php',{action:'change_status',id:id,status:status}, function(resp){
    if(resp.success) location.reload();
    else alert(resp.error || 'Error');
  });
}
function openCreate(){ document.getElementById('modal').style.display='flex'; document.getElementById('modalTitle').innerText='New Task'; document.getElementById('taskForm').reset(); document.getElementById('deleteBtn').style.display='none'; }
function openTask(id){
  ajax('tasks_api.php',{action:'fetch',id:id}, function(r){
    if(r.success){
      document.getElementById('modal').style.display='flex';
      document.getElementById('modalTitle').innerText='Edit Task';
      document.getElementById('task_id').value = r.task.id;
      document.getElementById('title').value = r.task.title;
      document.getElementById('desc').value = r.task.description || '';
      document.getElementById('priority').value = r.task.priority;
      document.getElementById('due').value = r.task.due_date || '';
      document.getElementById('assignee').value = r.task.assignee_id || '';
      document.getElementById('deleteBtn').style.display='inline-block';
      loadComments(id);
    } else alert('Task not found');
  });
}
function closeModal(){ document.getElementById('modal').style.display='none'; document.getElementById('commentsSection').innerHTML=''; }
function submitTaskForm(){
  const id = document.getElementById('task_id').value;
  const data = {
    action: id ? 'update' : 'create',
    id: id,
    title: document.getElementById('title').value,
    description: document.getElementById('desc').value,
    priority: document.getElementById('priority').value,
    due_date: document.getElementById('due').value,
    assignee_id: document.getElementById('assignee').value,
    project_id: '<?php echo $project_id;?>'
  };
  ajax('tasks_api.php', data, function(resp){
    if(resp.success) location.reload();
    else alert(resp.error || 'Error');
  });
  return false;
}
function deleteTask(){
  const id = document.getElementById('task_id').value;
  if(!confirm('Delete task?')) return;
  ajax('tasks_api.php',{action:'delete',id:id},function(r){ if(r.success) location.reload(); else alert(r.error); });
}
let commentsTimer = null;
function loadComments(taskId){
  ajax('comments_api.php',{action:'list',task_id:taskId}, function(r){
    if(r.success){
      const sec = document.getElementById('commentsSection'); sec.innerHTML='';
      r.comments.forEach(c=>{ const d=document.createElement('div'); d.className='comment'; d.innerHTML = '<strong>'+escapeHtml(c.user)+'</strong> <small style="color:#666">'+c.created_at+'</small><div>'+escapeHtml(c.body)+'</div>'; sec.appendChild(d); });
      if(commentsTimer) clearTimeout(commentsTimer);
      commentsTimer = setTimeout(()=> loadComments(taskId),5000);
    }
  });
}
function postComment(){
  const taskId = document.getElementById('task_id').value;
  const body = document.getElementById('commentText').value.trim();
  if(!body) return;
  ajax('comments_api.php',{action:'add',task_id:taskId,body:body}, function(r){ if(r.success){ document.getElementById('commentText').value=''; loadComments(taskId); } else alert(r.error); });
}
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
</script>
</body>
</html>
