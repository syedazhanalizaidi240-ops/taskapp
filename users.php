<?php
include 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') header("Location: index.php");
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $id = intval($_POST['user_id']);
    $dir = __DIR__ . '/uploads/';
    if (!file_exists($dir)) mkdir($dir,0777,true);
    $fn = basename($_FILES['avatar']['name']);
    if (move_uploaded_file($_FILES['avatar']['tmp_name'],$dir.$fn)) {
        $stmt = $conn->prepare("UPDATE users SET avatar=? WHERE id=?"); $stmt->bind_param("si",$fn,$id); $stmt->execute();
        $msg = "Avatar uploaded.";
    } else $msg = "Upload failed.";
}
$res = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Users</title>
<style>body{font-family:Arial;margin:0;background:#f4f6f8}nav{background:#222;width:220px;height:100vh;position:fixed;left:0;top:0;padding-top:60px}nav a{display:block;padding:12px;color:#ccc;text-decoration:none}.main{margin-left:240px;padding:20px}.card{background:#fff;padding:16px;border-radius:8px;margin-bottom:12px} img.avatar{width:48px;height:48px;border-radius:50%}</style>
</head><body>
<nav><a href="index.php">Dashboard</a><a href="projects.php">Projects</a></nav>
<div class="main">
  <div class="card"><h2>Users</h2><?php if($msg) echo "<div style='color:green'>{$msg}</div>"; ?>
    <table border="1" cellpadding="8" style="border-collapse:collapse;width:100%"><tr><th>ID</th><th>Avatar</th><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr>
      <?php while($u = $res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $u['id'];?></td>
          <td><?php if($u['avatar']): ?><img class="avatar" src="uploads/<?php echo $u['avatar'];?>"><?php endif;?></td>
          <td><?php echo htmlspecialchars($u['name']);?></td>
          <td><?php echo htmlspecialchars($u['email']);?></td>
          <td><?php echo $u['role'];?></td>
          <td>
            <form method="post" enctype="multipart/form-data" style="display:inline-block">
              <input type="file" name="avatar" required>
              <input type="hidden" name="user_id" value="<?php echo $u['id'];?>">
              <button type="submit">Upload</button>
            </form>
          </td>
        </tr>
      <?php endwhile;?>
    </table>
  </div>
</div>
</body></html>
