<?php
include 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $pass = $_POST['password'];
    $hash = hash('sha256', $pass);
    $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $hash);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $_SESSION['user'] = ['id' => $row['id'], 'name' => $row['name'], 'role' => $row['role']];
        header("Location: index.php");
        exit;
    } else {
        $msg = "Invalid credentials.";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login | Task Management</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }
    .login-card {
        background: #fff;
        padding: 40px 35px;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        width: 380px;
        animation: fadeIn 1s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 25px;
    }
    input {
        width: 100%;
        padding: 12px 14px;
        margin: 10px 0;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
        transition: border-color 0.3s;
    }
    input:focus {
        border-color: #2575fc;
        outline: none;
        box-shadow: 0 0 4px rgba(37,117,252,0.4);
    }
    button {
        width: 100%;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: white;
        padding: 12px;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 10px;
        transition: 0.3s;
    }
    button:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .error {
        background: #ffe6e6;
        color: #b30000;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
        text-align: center;
    }
    p {
        text-align: center;
        font-size: 0.9rem;
        margin-top: 15px;
    }
    a {
        color: #2575fc;
        text-decoration: none;
        font-weight: 600;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
    <div class="login-card">
        <h2>Login to Continue</h2>
        <?php if($msg): ?>
            <div class="error"><?php echo $msg; ?></div>
        <?php endif; ?>
        <form method="post">
            <input name="email" type="email" placeholder="Enter Email" required>
            <input name="password" type="password" placeholder="Enter Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Create one</a></p>
    </div>
</body>
</html>
