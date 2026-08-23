<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

require_once 'db.php';
require_once 'user_logger.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? '';
    $currentUserId = $_SESSION['user_id'] ?? '';
    $currentRole = $_SESSION['role'] ?? '';

    if (empty($action)) {
        throw new Exception('Invalid action');
    }

    // Prevent users from performing destructive actions on themselves
    $destructive_actions = ['block', 'unblock', 'delete', 'approve', 'reject'];
    if ($userId == $currentUserId && in_array($action, $destructive_actions)) {
        throw new Exception('You cannot perform this action on your own account');
    }

    // Convert userId to string if it's numeric to match database VARCHAR type
    $userId = (string) $userId;

    switch ($action) {
        case 'get_users':
            // Get users with filtering and pagination
            $search = trim($_POST['search'] ?? '');
            $role_filter = $_POST['role_filter'] ?? '';
            $status_filter = $_POST['status_filter'] ?? '';
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $where_clauses = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($search)) {
                $where_clauses[] = "(idNo LIKE ? OR username LIKE ? OR firstName LIKE ? OR lastName LIKE ? OR emailAddress LIKE ?)";
                $search_param = "%$search%";
                $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
                $types .= "sssss";
            }

            if (!empty($role_filter)) {
                $where_clauses[] = "role = ?";
                $params[] = $role_filter;
                $types .= "s";
            }

            if (!empty($status_filter)) {
                $where_clauses[] = "status = ?";
                $params[] = $status_filter;
                $types .= "s";
            }

            $where_sql = "WHERE " . implode(" AND ", $where_clauses);

            // Count total for pagination
            $count_query = "SELECT COUNT(*) as total FROM users $where_sql";
            $count_stmt = $connect->prepare($count_query);
            if (!empty($params)) {
                $count_stmt->bind_param($types, ...$params);
            }
            $count_stmt->execute();
            $total_users = $count_stmt->get_result()->fetch_assoc()['total'];
            $total_pages = ceil($total_users / $limit);

            // Fetch results
            $users_query = "SELECT idNo, username, firstName, middleName, lastName, extension, emailAddress, role, status, date_created FROM users $where_sql ORDER BY role DESC, lastName, firstName LIMIT ? OFFSET ?";
            $users_stmt = $connect->prepare($users_query);
            $pagination_params = array_merge($params, [$limit, $offset]);
            $pagination_types = $types . "ii";
            $users_stmt->bind_param($pagination_types, ...$pagination_params);
            $users_stmt->execute();
            $users_result = $users_stmt->get_result();

            $users = [];
            while ($user = $users_result->fetch_assoc()) {
                $users[] = $user;
            }

            $response = [
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'total_users' => $total_users,
                    'limit' => $limit
                ]
            ];
            break;

        case 'get_user':
            if (empty($userId)) {
                throw new Exception('User ID is required');
            }
            
            $stmt = $conn->prepare("SELECT idNo, username, firstName, middleName, lastName, extension, birthday, age, sex, emailAddress, purok, barangay, municipality, province, country, zipCode, role, status, date_created FROM users WHERE idNo = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                $response = [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                throw new Exception('User not found');
            }
            break;

        case 'create_user':
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['firstName'] ?? '');
            $middleName = trim($_POST['middleName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');
            $extension = trim($_POST['extension'] ?? '');
            $birthday = $_POST['birthday'] ?? '';
            $sex = $_POST['sex'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            $role = $_POST['role'] ?? '';
            $idNo = trim($_POST['idNo'] ?? '');
            $purok = trim($_POST['purok'] ?? '');
            $barangay = trim($_POST['barangay'] ?? '');
            $municipality = trim($_POST['municipality'] ?? '');
            $province = trim($_POST['province'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $zipCode = trim($_POST['zipCode'] ?? '');

            $errors = [];

            // Required validations
            if (empty($username)) $errors[] = 'Username is required';
            if (empty($firstName)) $errors[] = 'First name is required';
            if (empty($lastName)) $errors[] = 'Last name is required';
            if (empty($email)) $errors[] = 'Email is required';
            if (empty($password)) $errors[] = 'Password is required';
            if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
            if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
            if (empty($role)) $errors[] = 'Role is required';
            if (empty($idNo)) $errors[] = 'ID Number is required';
            if (empty($sex)) $errors[] = 'Sex is required';
            if (empty($birthday)) $errors[] = 'Birthday is required';
            if (empty($purok)) $errors[] = 'Purok/Street is required';
            if (empty($barangay)) $errors[] = 'Barangay is required';
            if (empty($municipality)) $errors[] = 'Municipality is required';
            if (empty($province)) $errors[] = 'Province is required';
            if (empty($country)) $errors[] = 'Country is required';
            if (empty($zipCode)) $errors[] = 'ZIP Code is required';

            if (!in_array($role, ['admin', 'super_admin', 'customer'])) {
                $errors[] = 'Invalid role';
            } elseif ($currentRole === 'admin' && $role === 'super_admin') {
                $errors[] = 'Admins cannot create Super Admin accounts';
            }

            if (!empty($username)) {
                $c = $conn->prepare("SELECT idNo FROM users WHERE username = ?");
                $c->bind_param("s", $username); $c->execute();
                if ($c->get_result()->num_rows > 0) $errors[] = 'Username already exists';
            }
            if (!empty($email)) {
                $c = $conn->prepare("SELECT idNo FROM users WHERE emailAddress = ?");
                $c->bind_param("s", $email); $c->execute();
                if ($c->get_result()->num_rows > 0) $errors[] = 'Email already exists';
            }
            if (!empty($idNo)) {
                $c = $conn->prepare("SELECT idNo FROM users WHERE idNo = ?");
                $c->bind_param("s", $idNo); $c->execute();
                if ($c->get_result()->num_rows > 0) $errors[] = 'ID Number already exists';
            }

            if (!empty($errors)) {
                throw new Exception(implode("\n", $errors));
            }

            // Calculate age
            $age = 0;
            if (!empty($birthday)) {
                $dob = new DateTime($birthday);
                $now = new DateTime();
                $age = $dob->diff($now)->y;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate next ID if not provided
            if (empty($idNo)) {
                $year = date('Y');
                $last_id_result = $conn->query("SELECT idNo FROM users WHERE idNo LIKE '$year-%' ORDER BY id DESC LIMIT 1");
                $last_id = $last_id_result && $last_id_result->num_rows > 0 ? $last_id_result->fetch_assoc()['idNo'] : '';
                if ($last_id) {
                    $parts = explode('-', $last_id);
                    $next_num = (int)$parts[1] + 1;
                    $idNo = $year . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
                } else {
                    $idNo = $year . '-0001';
                }
            }

            $ins = $conn->prepare("INSERT INTO users (idNo, username, firstName, middleName, lastName, extension, birthday, age, sex, emailAddress, password_hash, role, status, purok, barangay, municipality, province, country, zipCode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?)");
            $ins->bind_param("ssssssssisssssssss",
                $idNo, $username, $firstName, $middleName, $lastName, $extension,
                $birthday, $age, $sex, $email, $hashed, $role,
                $purok, $barangay, $municipality, $province, $country, $zipCode
            );
            if ($ins->execute()) {
                logAction('CREATE_USER', "User {$_SESSION['username']} created new user $username (ID: $idNo)");
                $response = ['success' => true, 'message' => 'User created successfully', 'user_id' => $idNo];
            } else {
                throw new Exception('Failed to create user');
            }
            break;

        case 'update_user':
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['firstName'] ?? '');
            $middleName = trim($_POST['middleName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');
            $extension = trim($_POST['extension'] ?? '');
            $birthday = $_POST['birthday'] ?? '';
            $sex = $_POST['sex'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? '';
            $purok = trim($_POST['purok'] ?? '');
            $barangay = trim($_POST['barangay'] ?? '');
            $municipality = trim($_POST['municipality'] ?? '');
            $province = trim($_POST['province'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $zipCode = trim($_POST['zipCode'] ?? '');
            $new_password = $_POST['new_password'] ?? '';

            if (empty($username) || empty($firstName) || empty($lastName) || empty($email) || empty($role) || empty($sex) || empty($birthday) || empty($purok) || empty($barangay) || empty($municipality) || empty($province) || empty($country) || empty($zipCode)) {
                throw new Exception('All required fields must be filled');
            }

            // Calculate age
            $age = 0;
            if (!empty($birthday)) {
                $dob = new DateTime($birthday);
                $now = new DateTime();
                $age = $dob->diff($now)->y;
            }

            // Validate role
            if (!in_array($role, ['admin', 'super_admin', 'customer'])) {
                throw new Exception('Invalid role');
            }
            if ($currentRole === 'admin' && $role === 'super_admin') {
                throw new Exception('Admins cannot grant Super Admin role');
            }
            
            // Check target user's current role
            $stmt_check = $conn->prepare("SELECT role FROM users WHERE idNo = ?");
            $stmt_check->bind_param("s", $userId);
            $stmt_check->execute();
            $target_user_role = $stmt_check->get_result()->fetch_assoc()['role'] ?? '';
            
            if ($currentRole === 'admin' && $target_user_role === 'super_admin') {
                throw new Exception('Admins cannot modify Super Admin accounts');
            }

            // Handle optional password update (super_admin only)
            if (!empty($new_password)) {
                if ($currentRole !== 'super_admin') {
                    throw new Exception('Only Super Admins can update user passwords');
                }
                if (strlen($new_password) < 8) {
                    throw new Exception('Password must be at least 8 characters');
                }
                $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, firstName = ?, middleName = ?, lastName = ?, extension = ?, sex = ?, birthday = ?, age = ?, emailAddress = ?, role = ?, purok = ?, barangay = ?, municipality = ?, province = ?, country = ?, zipCode = ?, password_hash = ? WHERE idNo = ?");
                $stmt->bind_param("sssssssissssssssss", $username, $firstName, $middleName, $lastName, $extension, $sex, $birthday, $age, $email, $role, $purok, $barangay, $municipality, $province, $country, $zipCode, $hashed_pw, $userId);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, firstName = ?, middleName = ?, lastName = ?, extension = ?, sex = ?, birthday = ?, age = ?, emailAddress = ?, role = ?, purok = ?, barangay = ?, municipality = ?, province = ?, country = ?, zipCode = ? WHERE idNo = ?");
                $stmt->bind_param("sssssssisssssssss", $username, $firstName, $middleName, $lastName, $extension, $sex, $birthday, $age, $email, $role, $purok, $barangay, $municipality, $province, $country, $zipCode, $userId);
            }

            if ($stmt->execute()) {
                $pw_msg = !empty($new_password) ? ' (password updated)' : '';
                logAction('UPDATE_USER', "User {$_SESSION['username']} updated user $username$pw_msg");
                $response = ['success' => true, 'message' => 'User updated successfully' . $pw_msg];
            } else {
                throw new Exception('Failed to update user');
            }
            break;

        case 'delete_user':
            // Only super_admin can delete
            if ($currentRole !== 'super_admin') {
                throw new Exception('Only super admins can delete user accounts');
            }

            // Get user details
            $stmt = $conn->prepare("SELECT username, role FROM users WHERE idNo = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $target_user = $result->fetch_assoc();

            if (!$target_user) {
                throw new Exception('User not found');
            }

            // Super admin cannot delete their own account
            if ($userId === $currentUserId) {
                throw new Exception('You cannot delete your own account');
            }

            // Delete the user
            $del_stmt = $conn->prepare("DELETE FROM users WHERE idNo = ?");
            $del_stmt->bind_param("s", $userId);
            if ($del_stmt->execute()) {
                logAction('DELETE_USER', "User {$_SESSION['username']} deleted user {$target_user['username']} (ID: $userId)");
                $response = ['success' => true, 'message' => "User {$target_user['username']} has been deleted successfully"];
            } else {
                throw new Exception('Failed to delete user');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
if (isset($connect) && $connect instanceof mysqli) {
    $connect->close();
}