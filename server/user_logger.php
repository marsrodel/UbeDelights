<?php
// User Activity Logger Helper Function
require_once 'db.php';

/**
 * Logs user activity to the user_logs table
 * @param string $action The action performed (e.g., LOGIN, LOGOUT, CREATE_USER)
 * @param string $description Human-readable description of the action
 * @param string|null $user_name User name (defaults to current session user, null for system actions)
 * @return bool True if logging successful, false otherwise
 */
function logAction($action, $description, $user_name = null) {
    global $connect;
    
    if (!$connect || $connect->connect_error) {
        error_log("Database connection failed for logging: " . ($connect->connect_error ?? 'unknown'));
        return false;
    }
    
    // Get user name from session if not provided
    if ($user_name === null && isset($_SESSION['username'])) {
        $user_name = $_SESSION['username'];
    }
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    
    // Get device and browser information
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device_info = getDeviceAndBrowser($user_agent);
    
    // Prepare and execute the insert statement
    $stmt = $connect->prepare("INSERT INTO user_logs (user_name, action, description, ip_address, device, browser) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        error_log("Failed to prepare log statement: " . $connect->error);
        return false;
    }
    
    $stmt->bind_param("ssssss", $user_name, $action, $description, $ip_address, $device_info['device'], $device_info['browser']);
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to execute log statement: " . $stmt->error);
    }
    
    $stmt->close();
    
    return $result;
}

/**
 * Parse user agent string to extract device and browser information
 * @param string $user_agent The HTTP_USER_AGENT string
 * @return array Array containing device and browser information
 */
function getDeviceAndBrowser($user_agent) {
    $device = 'Unknown';
    $browser = 'Unknown';
    $os = getOS($user_agent);
    
    // Detect browser
    if (preg_match('/Chrome\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Google Chrome ' . $matches[1];
    } elseif (preg_match('/Firefox\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Mozilla Firefox ' . $matches[1];
    } elseif (preg_match('/Safari\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Safari ' . $matches[1];
    } elseif (preg_match('/Edge\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Microsoft Edge ' . $matches[1];
    } elseif (preg_match('/Opera\/([0-9\.]+)/', $user_agent, $matches)) {
        $browser = 'Opera ' . $matches[1];
    } elseif (strpos($user_agent, 'MSIE') !== false) {
        $browser = 'Internet Explorer';
    }
    
    // Detect device type and create specific device names
    if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/', $user_agent)) {
        if (preg_match('/iPad/', $user_agent)) {
            $device = 'Device: iPad Tablet';
        } elseif (preg_match('/Android/', $user_agent)) {
            if (preg_match('/Mobile/', $user_agent)) {
                $device = 'Device: Android Phone';
            } else {
                $device = 'Device: Android Tablet';
            }
        } elseif (preg_match('/iPhone/', $user_agent)) {
            $device = 'Device: iPhone';
        } elseif (preg_match('/iPod/', $user_agent)) {
            $device = 'Device: iPod Touch';
        } else {
            $device = 'Device: Mobile Phone';
        }
    } elseif (preg_match('/Windows NT|Macintosh|Linux/', $user_agent)) {
        if (preg_match('/Windows NT/', $user_agent)) {
            $device = 'Device: Windows PC';
        } elseif (preg_match('/Macintosh/', $user_agent)) {
            $device = 'Device: Mac Computer';
        } elseif (preg_match('/Linux/', $user_agent)) {
            $device = 'Device: Linux PC';
        } else {
            $device = 'Device: Desktop Computer';
        }
    }
    
    return [
        'device' => $device,
        'browser' => $browser,
        'os' => $os
    ];
}

/**
 * Detect operating system from user agent
 * @param string $user_agent The HTTP_USER_AGENT string
 * @return string Operating system name
 */
function getOS($user_agent) {
    if (preg_match('/Windows NT/i', $user_agent)) return 'Windows';
    if (preg_match('/Macintosh|Mac OS X/i', $user_agent)) return 'MacOS';
    if (preg_match('/iPhone/i', $user_agent)) return 'iOS';
    if (preg_match('/Android/i', $user_agent)) return 'Android';
    if (preg_match('/Linux/i', $user_agent)) return 'Linux';
    return 'Unknown OS';
}

/**
 * Get current user's ID number from session
 * @return string|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's role from session
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if current user can perform action on target user
 * @param string $target_role Target user's role
 * @param string $action Action being performed
 * @return bool
 */
function canPerformAction($target_role, $action) {
    $current_role = getCurrentUserRole();
    $current_user_id = getCurrentUserId();
    
    // Prevent self-destructive actions
    if (in_array($action, ['block', 'unblock', 'delete', 'approve', 'reject'])) {
        // Note: We'd need to check if target is self, but we don't have target ID here
        // This check is done in user_actions.php
    }
    
    // Admin cannot modify super_admin
    if ($current_role === 'admin' && $target_role === 'super_admin') {
        return false;
    }
    
    // Admin cannot create super_admin
    if ($current_role === 'admin' && $action === 'create_super_admin') {
        return false;
    }
    
    // Only super_admin can delete
    if ($action === 'delete' && $current_role !== 'super_admin') {
        return false;
    }
    
    return true;
}