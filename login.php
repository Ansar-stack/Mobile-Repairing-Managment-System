<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header('Location: /mobile-repair/pages/dashboard/index.php');
    exit;
}

require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $sql  = "SELECT USER_ID, USERNAME, PASSWORD, ROLE FROM USERS WHERE USERNAME = '" . addslashes($username) . "' AND IS_DELETED = 0";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        if ($row && password_verify($password, $row['PASSWORD'])) {
            if (strtolower($row['ROLE']) !== 'admin') {
                $error = 'Access denied. Admin only.';
            } else {
                $_SESSION['user'] = [
                    'id'       => $row['USER_ID'],
                    'username' => $row['USERNAME'],
                    'role'     => $row['ROLE'],
                ];
                oci_close($conn);
                header('Location: /mobile-repair/pages/dashboard/index.php');
                exit;
            }
        } else {
            $error = 'Invalid username or password.';
        }
        oci_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Mobile Repair System</title>
    <link rel="stylesheet" href="/mobile-repair/assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-box">
        <h2>Mobile Repair System</h2>
        <h3>Admin Login</h3>
        <?php if ($error !== ''): ?>
            <div class="msg error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
