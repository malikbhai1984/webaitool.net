<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$host     = "localhost";
$dbname   = "u893493446_webaitool";
$username = "u893493446_shahid3";
$password = "Sj4143086*";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
}

try {
    // --- existing tables ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS parent_menus (
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT '#', position INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sub_menus (
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL,
        link VARCHAR(255) NOT NULL, parent_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_parent (parent_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tool_items (
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL,
        icon VARCHAR(50) DEFAULT '🔧', link VARCHAR(255) NOT NULL,
        description TEXT, image LONGTEXT, parent_id INT DEFAULT NULL,
        position INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pos (position), INDEX idx_parent (parent_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE INDEX idx_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS footer_sections (
        id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL, position INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // --- NEW: ticker items ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS ticker_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        icon VARCHAR(50) DEFAULT '🔧',
        link VARCHAR(255) NOT NULL,
        badge VARCHAR(80) DEFAULT '',
        is_active TINYINT(1) DEFAULT 1,
        position INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // --- NEW: floating cards ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS floating_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        icon VARCHAR(50) DEFAULT '🛠️',
        color_from VARCHAR(30) DEFAULT '#1a1a2e',
        color_to   VARCHAR(30) DEFAULT '#0f3460',
        accent     VARCHAR(30) DEFAULT '#6c5ce7',
        position INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // --- NEW: floating card links ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS floating_card_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        card_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        icon VARCHAR(50) DEFAULT '🔗',
        link VARCHAR(255) NOT NULL,
        badge VARCHAR(80) DEFAULT '',
        position INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_card (card_id), INDEX idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // --- Seed default data ---
    $count = $pdo->query("SELECT COUNT(*) as c FROM site_settings")->fetch();
    if ($count['c'] == 0) {
        $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES
            ('cards_per_row',    '3'),
            ('footer_text',      '© 2024 WebAITool.net | All Rights Reserved'),
            ('ticker_speed',     '28'),
            ('ticker_font_size', '14'),
            ('ticker_label',     '🔥 Popular'),
            ('ticker_label_show','1'),
            ('hero_title',       'Complete AI Toolkit'),
            ('hero_title_font',  '38'),
            ('hero_subtitle',    '40+ Free Tools | No Registration | 100% Private'),
            ('hero_sub_font',    '18'),
            ('cards_section_title','⚡ Featured Tool Categories'),
            ('cards_title_font', '20'),
            ('show_link_arrow',  '1'),
            ('show_card_icon',   '1'),
            ('show_link_icon',   '1'),
            ('show_ticker_icon', '1'),
            ('tooltip_font_size','11'),
            ('nav_gap',          '2'),
            ('nav_font',         '13'),
            ('nav_brand_font',   '20'),
            ('nav_brand_margin', '10'),
            ('nav_height',       '58'),
            ('nav_side_pad',     '20'),
            ('nav_pad_tb',       '6'),
            ('nav_pad_lr',       '11'),
            ('nav_radius',       '7'),
            ('nav_hover_color',  '#6c5ce7'),
            ('page_side_pad',    '20'),
            ('page_max_width',   '1280'),
            -- Tool card colors
            ('tc_bg',            '#ffffff'),
            ('tc_bg1',           '#ffffff'),
            ('tc_bg2',           '#ffffff'),
            ('tc_grad',          '0'),
            ('tc_title',         '#1e1b4b'),
            ('tc_desc',          '#64748b'),
            ('tc_btn',           '#6c5ce7'),
            -- Cards strip colors
            ('cs_bg',            'transparent'),
            ('cs_bg1',           '#ffffff'),
            ('cs_bg2',           '#ffffff'),
            ('cs_grad',          '0'),
            ('cs_title',         '#2d3436'),
            -- Floating card colors
            ('fc_bg',            '#ffffff'),
            ('fc_bg1',           '#ffffff'),
            ('fc_bg2',           '#ffffff'),
            ('fc_grad',          '0'),
            ('fc_title',         '#1e1b4b'),
            ('card_width',       '220'),
            ('cards_speed',      '30'),
            ('card_title_font',  '12'),
            ('card_link_font',   '11'),
            ('card_icon_size',   '18'),
            ('card_link_icon',   '15'),
            ('initialized',      '1')");

        $pdo->exec("INSERT INTO parent_menus (name, link, position) VALUES
            ('Home','index.html',0),('Tools','#',1),('Contact','contact.html',2)");

        $toolsId = $pdo->query("SELECT id FROM parent_menus WHERE name='Tools' LIMIT 1")->fetch()['id'];
        $pdo->exec("INSERT INTO sub_menus (name, link, parent_id) VALUES
            ('Image Tools','image-tools.html',$toolsId),
            ('PDF Tools','pdf-tools.html',$toolsId),
            ('QR Tools','qr-tools.html',$toolsId)");

        $pdo->exec("INSERT INTO tool_items (name,icon,link,description,position) VALUES
            ('Image Tools','🖼️','image-tools.html','Resize, compress, convert images',0),
            ('QR Tools','📱','qr-tools.html','Generate & scan QR codes',1),
            ('PDF Tools','📄','pdf-tools.html','Compress, merge, split PDFs',2),
            ('YouTube SEO','📺','youtube-tools.html','Optimize YouTube content',3),
            ('Business Tools','💼','business-tools.html','Invoice, resume, calculators',4),
            ('Developer Tools','💻','dev-tools.html','JSON, Base64, passwords',5)");

        // seed ticker
        $pdo->exec("INSERT INTO ticker_items (name,icon,link,badge,position) VALUES
            ('Image Compressor','🖼️','image-tools.html','Hot',0),
            ('QR Generator','📱','qr-tools.html','Free',1),
            ('PDF Merger','📄','pdf-tools.html','New',2),
            ('YouTube Tools','📺','youtube-tools.html','',3),
            ('Password Gen','🔐','dev-tools.html','Secure',4),
            ('Business Tools','💼','business-tools.html','',5)");

        // seed floating cards
        $pdo->exec("INSERT INTO floating_cards (title,icon,color_from,color_to,accent,position) VALUES
            ('PDF Tools','📄','#1a1a2e','#0f3460','#ef4444',0),
            ('Image Tools','🖼️','#0d1b2a','#1b4332','#10b981',1),
            ('Dev Tools','💻','#1a0533','#2d1b69','#8b5cf6',2)");

        $c1 = $pdo->query("SELECT id FROM floating_cards WHERE title='PDF Tools' LIMIT 1")->fetch()['id'];
        $c2 = $pdo->query("SELECT id FROM floating_cards WHERE title='Image Tools' LIMIT 1")->fetch()['id'];
        $c3 = $pdo->query("SELECT id FROM floating_cards WHERE title='Dev Tools' LIMIT 1")->fetch()['id'];

        $pdo->exec("INSERT INTO floating_card_links (card_id,name,icon,link,badge,position) VALUES
            ($c1,'PDF Compressor','📦','pdf-tools.html','Hot',0),
            ($c1,'PDF Merger','🔗','pdf-tools.html','',1),
            ($c1,'PDF to Word','📝','pdf-tools.html','New',2),
            ($c1,'PDF Splitter','✂️','pdf-tools.html','',3),
            ($c1,'PDF Viewer','👁️','pdf-tools.html','',4),
            ($c2,'Image Compressor','🗜️','image-tools.html','Hot',0),
            ($c2,'Image Resizer','📐','image-tools.html','',1),
            ($c2,'BG Remover','✨','image-tools.html','New',2),
            ($c2,'Format Converter','🔄','image-tools.html','',3),
            ($c2,'Image Crop','✂️','image-tools.html','',4),
            ($c3,'JSON Formatter','{}','dev-tools.html','',0),
            ($c3,'Base64 Encoder','🔒','dev-tools.html','',1),
            ($c3,'Password Gen','🔑','dev-tools.html','Secure',2),
            ($c3,'URL Encoder','🌐','dev-tools.html','',3),
            ($c3,'Regex Tester','🔍','dev-tools.html','',4)");
    } else {
        // add missing keys if not present
        $keys = $pdo->query("SELECT setting_key FROM site_settings")->fetchAll(PDO::FETCH_COLUMN);
        $newKeys = [
            'ticker_speed'    => '28',
            'ticker_font_size'=> '14',
            'ticker_label'       => '🔥 Popular',
            'ticker_label_show'  => '1',
            'hero_title'         => 'Complete AI Toolkit',
            'hero_title_font'    => '38',
            'hero_subtitle'      => '40+ Free Tools | No Registration | 100% Private',
            'hero_sub_font'      => '18',
            'cards_section_title'=> '⚡ Featured Tool Categories',
            'cards_title_font'   => '20',
            'show_link_arrow'    => '1',
            'show_card_icon'     => '1',
            'show_link_icon'     => '1',
            'show_ticker_icon'   => '1',
            'tooltip_font_size'  => '11',
            'nav_gap'            => '2',
            'nav_font'           => '13',
            'nav_brand_font'     => '20',
            'nav_brand_margin'   => '10',
            'nav_height'         => '58',
            'nav_side_pad'       => '20',
            'nav_pad_tb'         => '6',
            'nav_pad_lr'         => '11',
            'nav_radius'         => '7',
            'nav_hover_color'    => '#6c5ce7',
            'page_side_pad'      => '20',
            'page_max_width'     => '1280',
            'card_width'      => '220',
            'cards_speed'     => '30',
            'card_title_font' => '12',
            'card_link_font'  => '11',
            'card_icon_size'  => '18',
            'card_link_icon'  => '15',
        ];
        foreach ($newKeys as $k => $v) {
            if (!in_array($k, $keys)) {
                $pdo->prepare("INSERT INTO site_settings (setting_key,setting_value) VALUES (?,?)")->execute([$k,$v]);
            }
        }
    }
} catch(PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'DB init failed: ' . $e->getMessage()]));
}
?>
