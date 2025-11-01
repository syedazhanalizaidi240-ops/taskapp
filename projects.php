<?php
include 'db.php';
if (!isset($_SESSION['user'])) header("Location: login.php");
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $desc  = $conn->real_escape_string($_POST['description']);
    $owner = intval($_SESSION['user']['id']);
    if ($title=='') $msg = "Please provide a project title.";
    else {
        $ins = $conn->prepare("INSERT INTO projects (title,description,owner_id) VALUES (?,?,?)");
        $ins->bind_param("ssi",$title,$desc,$owner);
        $ins->execute();
        header("Location: projects.php");
        exit;
    }
}
$projects = $conn->query("SELECT p.*, u.name as owner FROM projects p LEFT JOIN users u ON p.owner_id=u.id ORDER BY p.created_at DESC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Projects | Task Manager</title>
<style>
    *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',sans-serif}
    body{
        background:#f5f7fb;
        display:flex;
        min-height:100vh;
    }
    header{
        position:fixed;
        top:0; left:220px;
        right:0;
        background:#2575fc;
        color:#fff;
        padding:16px 30px;
        font-size:22px;
        font-weight:600;
        box-shadow:0 2px 8px rgba(0,0,0,0.1);
        z-index:10;
    }
    nav{
        width:220px;
        background:#1e1e2f;
        color:#fff;
        padding-top:70px;
        position:fixed;
        height:100vh;
        box-shadow:2px 0 8px rgba(0,0,0,0.1);
    }
    nav a{
        display:block;
        color:#ccc;
        padding:14px 20px;
        text-decoration:none;
        transition:0.3s;
        font-size:15px;
    }
    nav a:hover{
        background:#2575fc;
        color:#fff;
        padding-left:25px;
    }
    .main{
        margin-left:240px;
        padding:100px 40px 40px;
        flex-grow:1;
    }
    .card{
        background:#fff;
        padding:25px;
        border-radius:12px;
        box-shadow:0 4px 10px rgba(0,0,0,0.08);
        margin-bottom:25px;
        animation:fadeIn 0.7s ease-in-out;
    }
    @keyframes fadeIn {
        from{opacity:0;transform:translateY(20px)}
        to{opacity:1;transform:translateY(0)}
    }
    h2{
        color:#333;
        margin-bottom:10px;
    }
    input, textarea{
        width:100%;
        padding:10px;
        margin:10px 0;
        border-radius:8px;
        border:1px solid #ccc;
        font-size:15px;
        transition:0.2s;
    }
    input:focus, textarea:focus{
        border-color:#2575fc;
        outline:none;
        box-shadow:0 0 4px rgba(37,117,252,0.4);
    }
    button{
        background:linear-gradient(135deg,#6a11cb,#2575fc);
        color:#fff;
        padding:10px 18px;
        border:none;
        border-radius:8px;
        cursor:pointer;
        font-size:15px;
        transition:0.3s;
    }
    button:hover{
        transform:scale(1.05);
        box-shadow:0 4px 10px rgba(37,117,252,0.3);
    }
    ul{list-style:none;padding-left:0;margin-top:10px}
    li{
        background:#f9f9f9;
        padding:12px 15px;
        margin-bottom:8px;
        border-radius:8px;
        box-shadow:0 2px 5px rgba(0,0,0,0.05);
        transition:0.3s;
    }
    li:hover{transform:translateX(5px);background:#eef5ff}
    li a{
        color:#2575fc;
        font-weight:600;
        text-decoration:none;
    }
    li small{
        color:#555;
        font-size:14px;
    }
    .msg{
        color:#d93025;
        background:#ffe6e6;
        padding:10px;
        border-radius:8px;
        margin-bottom:10px;
        font-weight:500;
    }
</style>
</head>
<body>
<nav>
  <a href="index.php">🏠 Dashboard</a>
  <a href="projects.php">📁 Projects</a>
  <a href="analytics.php">📊 Analytics</a>
  <a href="users.php">👥 Users</a>
  <a href="logout.php">🚪 Logout (<?php echo htmlspecialchars($_SESSION['user']['name']); ?>)</a>
</nav>

<header>📁 Projects Management</header>

<div class="main">
  <div class="card">
    <h2>Create New Project</h2>
    <?php if($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <form method="post">
      <input name="title" placeholder="Project Title" required>
      <textarea name="description" placeholder="Project Description..." rows="4"></textarea>
      <button type="submit">➕ Create Project</button>
    </form>
  </div>

  <div class="card">
    <h2>All Projects</h2>
    <ul>
      <?php 
      if ($projects->num_rows > 0) {
        while ($p = $projects->fetch_assoc()) {
          echo "<li><a href='project_view.php?id={$p['id']}'>".htmlspecialchars($p['title'])."</a><br><small>Owner: ".htmlspecialchars($p['owner'])."</small></li>";
        }
      } else {
        echo "<li>No projects available.</li>";
      }
      ?>
    </ul>
  </div>
</div>
</body>
</html>
