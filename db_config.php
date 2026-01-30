<?php
// Database Configuration - Hostinger
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Your Hostinger Database Credentials
$host = "localhost";
$dbname = "u893493446_webaitool";
$username = "u893493446_shahid3";
$password = "Sj4143086*";

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    error_log("✅ Database connected successfully to: " . $dbname);
} catch(PDOException $e) {
    error_log("❌ Database connection failed: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]));
}

// Create tables if they don't exist
try {
    // Parent Menus Table (Create first - no dependencies)
    $pdo->exec("CREATE TABLE IF NOT EXISTS parent_menus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT '#',
        position INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_position (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Sub Menus Table (Depends on parent_menus - NO FOREIGN KEY)
    $pdo->exec("CREATE TABLE IF NOT EXISTS sub_menus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        link VARCHAR(255) NOT NULL,
        parent_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_parent (parent_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tool Items Table (No foreign keys)
    $pdo->exec("CREATE TABLE IF NOT EXISTS tool_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        icon VARCHAR(50) DEFAULT '🔧',
        link VARCHAR(255) NOT NULL,
        description TEXT,
        image LONGTEXT,
        parent_id INT DEFAULT NULL,
        position INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_position (position),
        INDEX idx_parent (parent_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Site Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE INDEX idx_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    error_log("✅ Tables created/verified successfully");

    // Check if we need to initialize default data
    $checkSettings = $pdo->query("SELECT COUNT(*) as count FROM site_settings")->fetch();
    
    if ($checkSettings['count'] == 0) {
        error_log("🔄 Initializing default data...");
        
        // Insert default settings
        $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES 
            ('cards_per_row', '3'),
            ('footer_text', '© 2024 WebAITool.net | All Rights Reserved'),
            ('initialized', '1')");
        
        // Insert default parent menus
        $pdo->exec("INSERT INTO parent_menus (name, link, position) VALUES 
            ('Home', 'index.html', 0),
            ('Tools', '#', 1),
            ('Contact', 'contact.html', 2)");
        
        // Get Tools parent menu ID
        $toolsMenu = $pdo->query("SELECT id FROM parent_menus WHERE name = 'Tools' ORDER BY id DESC LIMIT 1")->fetch();
        
        if($toolsMenu) {
            $toolsParentId = $toolsMenu['id'];
            
            // Insert default sub-menus
            $pdo->exec("INSERT INTO sub_menus (name, link, parent_id) VALUES 
                ('Image Tools', 'image-tools.html', $toolsParentId),
                ('PDF Tools', 'pdf-tools.html', $toolsParentId),
                ('QR Tools', 'qr-tools.html', $toolsParentId)");
        }
        
        // Insert default tools
        $pdo->exec("INSERT INTO tool_items (name, icon, link, description, position) VALUES 
            ('🖼️ Image Tools', '🖼️', 'image-tools.html', 'Resize, compress, convert images', 0),
            ('📱 QR Tools', '📱', 'qr-tools.html', 'Generate & scan QR codes', 1),
            ('📄 PDF Tools', '📄', 'pdf-tools.html', 'Compress, merge, split PDFs', 2),
            ('📺 YouTube SEO', '📺', 'youtube-tools.html', 'Optimize YouTube content', 3),
            ('💼 Business Tools', '💼', 'business-tools.html', 'Invoice, resume, calculators', 4),
            ('💻 Developer Tools', '💻', 'dev-tools.html', 'JSON, Base64, passwords', 5)");
        
        error_log("✅ Default data initialized successfully");
    }
    
} catch(PDOException $e) {
    error_log("❌ Table creation failed: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database initialization failed', 'details' => $e->getMessage()]));
}

error_log("✅ db_config.php loaded successfully");
?>
