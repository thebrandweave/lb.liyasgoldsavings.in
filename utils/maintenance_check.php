<?php
/**
 * Maintenance Check Utility
 * Include this file at the top of any page to enforce maintenance mode
 */

// Include maintenance configuration
require_once __DIR__ . '/../config/maintenance.php';

// Check if maintenance is enabled and user is not allowed to bypass
if (is_maintenance_enabled() && !is_ip_allowed()) {
    // Redirect to maintenance page
    header('Location: /maintenance.php');
    exit();
}
?>
