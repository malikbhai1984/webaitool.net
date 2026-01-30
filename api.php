<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

error_log("📌 API Called: " . $action);

try {
    switch($action) {
        
        // ==================== PARENT MENUS ====================
        case 'get_parent_menus':
            $stmt = $pdo->query("SELECT * FROM parent_menus ORDER BY position, id");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'add_parent_menu':
            $name = trim($_POST['name'] ?? '');
            $link = trim($_POST['link'] ?? '#');
            
            if(empty($name)) {
                throw new Exception('Menu name is required');
            }
            
            $maxPos = $pdo->query("SELECT COALESCE(MAX(position), -1) as max_pos FROM parent_menus")->fetch();
            $newPos = $maxPos['max_pos'] + 1;
            
            $stmt = $pdo->prepare("INSERT INTO parent_menus (name, link, position) VALUES (?, ?, ?)");
            $stmt->execute([$name, $link, $newPos]);
            
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'update_parent_menu':
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $link = trim($_POST['link'] ?? '#');
            
            if(empty($id) || empty($name)) {
                throw new Exception('Invalid data');
            }
            
            $stmt = $pdo->prepare("UPDATE parent_menus SET name = ?, link = ? WHERE id = ?");
            $stmt->execute([$name, $link, $id]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'delete_parent_menu':
            $id = $_POST['id'] ?? 0;
            
            if(empty($id)) {
                throw new Exception('Invalid ID');
            }
            
            $pdo->prepare("DELETE FROM sub_menus WHERE parent_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM parent_menus WHERE id = ?")->execute([$id]);
            
            echo json_encode(['success' => true]);
            break;
            
        // ==================== SUB MENUS ====================
        case 'get_sub_menus':
            $stmt = $pdo->query("SELECT * FROM sub_menus ORDER BY parent_id, id");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'add_sub_menu':
            $name = trim($_POST['name'] ?? '');
            $link = trim($_POST['link'] ?? '');
            $parent = $_POST['parent'] ?? 0;
            
            if(empty($name) || empty($link) || empty($parent)) {
                throw new Exception('All fields are required');
            }
            
            $stmt = $pdo->prepare("INSERT INTO sub_menus (name, link, parent_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $link, $parent]);
            
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'update_sub_menu':
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $link = trim($_POST['link'] ?? '');
            $parent = $_POST['parent'] ?? 0;
            
            if(empty($id) || empty($name) || empty($link) || empty($parent)) {
                throw new Exception('Invalid data');
            }
            
            $stmt = $pdo->prepare("UPDATE sub_menus SET name = ?, link = ?, parent_id = ? WHERE id = ?");
            $stmt->execute([$name, $link, $parent, $id]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'delete_sub_menu':
            $id = $_POST['id'] ?? 0;
            
            if(empty($id)) {
                throw new Exception('Invalid ID');
            }
            
            $pdo->prepare("DELETE FROM sub_menus WHERE id = ?")->execute([$id]);
            
            echo json_encode(['success' => true]);
            break;
            
        // ==================== TOOLS ====================
        case 'get_tools':
            $stmt = $pdo->query("SELECT * FROM tool_items ORDER BY position, id");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'add_tool':
            $name = trim($_POST['name'] ?? '');
            $icon = trim($_POST['icon'] ?? '🔧');
            $link = trim($_POST['link'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $image = $_POST['image'] ?? '';
            $parent = !empty($_POST['parent']) ? $_POST['parent'] : null;
            
            if(empty($name) || empty($link) || empty($desc)) {
                throw new Exception('Name, link and description required');
            }
            
            $maxPos = $pdo->query("SELECT COALESCE(MAX(position), -1) as max_pos FROM tool_items")->fetch();
            $newPos = $maxPos['max_pos'] + 1;
            
            $stmt = $pdo->prepare("INSERT INTO tool_items (name, icon, link, description, image, parent_id, position) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $icon, $link, $desc, $image, $parent, $newPos]);
            
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'update_tool':
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $icon = trim($_POST['icon'] ?? '🔧');
            $link = trim($_POST['link'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $image = $_POST['image'] ?? '';
            $parent = !empty($_POST['parent']) ? $_POST['parent'] : null;
            
            if(empty($id) || empty($name) || empty($link) || empty($desc)) {
                throw new Exception('Invalid data');
            }
            
            $stmt = $pdo->prepare("UPDATE tool_items SET name = ?, icon = ?, link = ?, description = ?, image = ?, parent_id = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $link, $desc, $image, $parent, $id]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'delete_tool':
            $id = $_POST['id'] ?? 0;
            
            if(empty($id)) {
                throw new Exception('Invalid ID');
            }
            
            $pdo->prepare("DELETE FROM tool_items WHERE id = ?")->execute([$id]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'reorder_tools':
            $order = json_decode($_POST['order'] ?? '[]', true);
            
            if(!is_array($order)) {
                throw new Exception('Invalid order data');
            }
            
            foreach($order as $index => $id) {
                $pdo->prepare("UPDATE tool_items SET position = ? WHERE id = ?")->execute([$index, $id]);
            }
            
            echo json_encode(['success' => true]);
            break;
            
        // ==================== SETTINGS ====================
        case 'get_settings':
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = [];
            foreach($stmt->fetchAll() as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            echo json_encode($settings);
            break;
            
        case 'update_setting':
            $key = $_POST['key'] ?? '';
            $value = $_POST['value'] ?? '';
            
            if(empty($key)) {
                throw new Exception('Setting key required');
            }
            
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch(Exception $e) {
    error_log("❌ API Error in {$action}: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
}
?>
