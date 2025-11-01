<?php
include 'db.php';
if (!isset($_SESSION['user'])) header("Location: login.php");
$uid = $_SESSION['user']['id'];

// load quick stats
$total_projects = $conn->query("SELECT COUNT(*) AS c FROM projects")->fetch_assoc()['c'];
$total_tasks = $conn->query("SELECT COUNT(*) AS c FROM tasks")->fetch_assoc()['c'];
$done_tasks = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE status='done'")->fetch_assoc()['c'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard | Task Management</title>
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
        padding:20px 25px;
        border-radius:12px;
        box-shadow:0 4px 10px rgba(0,0,0,0.08);
        margin-bottom:25px;
        animation:fadeIn 0.7s ease-in-out;
    }
    @keyframes fadeIn {
        from{opacity:0;transform:translateY(20px)}
        to{opacity:1;transform:translateY(0)}
    }
    h2,h3{color:#333;margin-bottom:10px}
    .stats{
        display:flex;
        gap:20px;
        flex-wrap:wrap;
    }
    .stat{
        flex:1;
        min-width:200px;
        background:linear-gradient(135deg,#6a11cb,#2575fc);
        color:#fff;
        padding:20px;
        border-radius:12px;
        text-align:center;
        box-shadow:0 4px 10px rgba(0,0,0,0.15);
        transition:transform 0.3s;
    }
    .stat:hover{
        transform:scale(1.05);
    }
    .stat strong{
        font-size:28px;
        display:block;
        margin-bottom:6px;
    }
    ul{
        list-style:none;
        padding-left:0;
    }
    li{
        padding:10px 0;
        border-bottom:1px solid #eee;
    }
    li a{
        color:#2575fc;
        text-decoration:none;
        font-weight:500;
    }
    li a:hover{
        text-decoration:underline;
    }
    span.owner{
        color:#666;
        font-size:14px;
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

<header>📌 Task Management Dashboard</header>

<div class="main">
  <div class="card">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</h2>
    <p style="color:#666;">Here’s a quick overview of your workspace.</p>
  </div>

  <div class="card">
    <div class="stats">
        <div class="stat">
            <strong><?php echo $total_projects; ?></strong>
            <div>Projects</div>
        </div>
        <div class="stat">
            <strong><?php echo $total_tasks; ?></strong>
            <div>Total Tasks</div>
        </div>
        <div class="stat">
            <strong><?php echo $done_tasks; ?></strong>
            <div>Completed</div>
        </div>
    </div>
  </div>

  <div class="card">
    <h3>Recent Projects</h3>
    <ul>
      <?php
      $res = $conn->query("SELECT p.*, u.name as owner FROM projects p LEFT JOIN users u ON p.owner_id=u.id ORDER BY p.created_at DESC LIMIT 6");
      if ($res->num_rows > 0) {
        while ($p = $res->fetch_assoc()) {
            echo "<li><a href='project_view.php?id={$p['id']}'>{$p['title']}</a> — <span class='owner'>{$p['owner']}</span></li>";
        }
      } else {
        echo "<li>No projects found.</li>";
      }
      ?>
    </ul>
  </div>
</div>
</body>
</html>
