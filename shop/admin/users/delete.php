<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';
require_once '../../config/UserManager.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: registered.php?error=1');
    exit();
}
$user_id = (int)$_GET['id'];
$userManager = new UserManager();
if ($userManager->deleteShopUser($user_id)) {
    header('Location: registered.php?deleted=1');
    exit();
} else {
    header('Location: registered.php?error=1');
    exit();
} 