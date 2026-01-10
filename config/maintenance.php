<?php
/**
 * Maintenance Mode Configuration
 * Controls website maintenance mode for all panels
 */

// Maintenance mode status (true = enabled, false = disabled)
$maintenance_enabled = true;

// Maintenance message
$maintenance_message = "Website is currently under maintenance. Please check back later.";

// Maintenance schedule (optional)
$maintenance_start = "2024-01-01 00:00:00";
$maintenance_end = "2024-12-31 23:59:59";

// Allowed IP addresses (bypass maintenance)
$allowed_ips = [
    // '127.0.0.1',
    // '::1',
    // Add your IP here if needed
];

/**
 * Check if maintenance mode is enabled
 */
function is_maintenance_enabled() {
    global $maintenance_enabled;
    return $maintenance_enabled;
}

/**
 * Check if current IP is allowed to bypass maintenance
 */
function is_ip_allowed() {
    global $allowed_ips;
    $current_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($current_ip, $allowed_ips);
}

/**
 * Enable maintenance mode
 */
function enable_maintenance() {
    global $maintenance_enabled;
    
    $config_file = __DIR__ . '/maintenance.php';
    $content = file_get_contents($config_file);
    
    // Change from false to true
    $content = preg_replace('/\$maintenance_enabled\s*=\s*false;/', '$maintenance_enabled = true;', $content);
    
    $result = file_put_contents($config_file, $content);
    
    if ($result !== false) {
        // Update the global variable immediately
        $maintenance_enabled = true;
        return true;
    }
    
    return false;
}

/**
 * Disable maintenance mode
 */
function disable_maintenance() {
    global $maintenance_enabled;
    
    $config_file = __DIR__ . '/maintenance.php';
    $content = file_get_contents($config_file);
    
    // Change from true to false
    $content = preg_replace('/\$maintenance_enabled\s*=\s*true;/', '$maintenance_enabled = true;', $content);
    
    $result = file_put_contents($config_file, $content);
    
    if ($result !== false) {
        // Update the global variable immediately
        $maintenance_enabled = true;
        return true;
    }
    
    return false;
}
?>
