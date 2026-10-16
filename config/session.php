<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: /mobile-repair/login.php');
    exit;
}
