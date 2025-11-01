<?php
include 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $pass = $_POST['password'];
    $role = in_array($_POST['role'] ?? 'member',['admin','manager','member']) ? $_POST['role'] : 'member';

    if ($name=='' || $email=='' || $pass=='') {
        $message = "Please fill all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows>0) { 
            $message = "Email already registered."; 
        } else {
            $hash = hash('sha256',$pass);
            $ins = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $ins->bind_param("ssss",$name,$email,$hash,$role);
            if ($ins->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $message = "DB error: ".$conn->error;
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Registration</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 1.2s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .card {
    background: #fff;
    width: 400px;
    padding: 30px 35px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
  }

  .card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 35px rgba(0,0,0,0.25);
  }

  h2 {
    text-align: center;
    color: #333;
    margin-bottom: 18px;
  }

  input, select {
    width: 100%;
    padding: 12px;
    margin: 8px 0 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    transition: 0.2s;
  }

  input:focus, select:focus {
    border-color: #4facfe;
    box-shadow: 0 0 6px rgba(79,172,254,0.5);
    outline: none;
  }

  button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    border: none;
    color: white;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
  }

  button:hover {
    background: linear-gradient(135deg, #00c6ff, #0072ff);
    transform: scale(1.03);
  }

  .msg {
    padding: 10px;
    margin-bottom: 14px;
    border-radius: 8px;
    font-size: 14px;
    text-align: center;
  }

  .error { background: #ffe6e6; color: #a00; border: 1px solid #ffbaba; }
  .success { background: #e6ffe6; color: #0a0; border: 1px solid #baffba; }

  p {
    text-align: center;
    font-size: 0.9rem;
    margin-top: 12px;
  }

  a {
    color: #007bff;
    text-decoration: none;
  }

  a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="card">
  <h2>✨ Create Your Account</h2>

  <?php if($message): ?>
    <div class="msg error"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <form method="post">
    <input name="name" placeholder="Full Name" required>
    <input name="email" placeholder="Email" type="email" required>
    <input name="password" placeholder="Password" type="password" required>
    <select name="role">
      <option value="member">Member</option>
      <option value="manager">Manager</option>
    </select>
    <button type="submit">Register</button>
  </form>

  <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
