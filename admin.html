<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Helper: bump data_version timestamp whenever any data changes
// index.html checks this version — if changed, fetches fresh data
function bumpVersion($pdo){
    $v = time(); // unix timestamp as version
    $pdo->prepare("INSERT INTO site_settings(setting_key,setting_value) VALUES('data_version',?) ON DUPLICATE KEY UPDATE setting_value=?")
        ->execute([$v,$v]);
}

require_once 'db_config.php';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try { switch($action) {

// ==================== PARENT MENUS ====================
case 'get_parent_menus':
    echo json_encode($pdo->query("SELECT * FROM parent_menus ORDER BY position,id")->fetchAll()); break;

case 'add_parent_menu':
    $name = trim($_POST['name']??''); $link = trim($_POST['link']??'#');
    if(!$name) throw new Exception('Name required');
    $pos = $pdo->query("SELECT COALESCE(MAX(position),-1)+1 as p FROM parent_menus")->fetch()['p'];
    $pdo->prepare("INSERT INTO parent_menus(name,link,position) VALUES(?,?,?)")->execute([$name,$link,$pos]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); break;

case 'update_parent_menu':
    $id=$_POST['id']??0; $name=trim($_POST['name']??''); $link=trim($_POST['link']??'#');
    if(!$id||!$name) throw new Exception('Invalid data');
    $pdo->prepare("UPDATE parent_menus SET name=?,link=? WHERE id=?")->execute([$name,$link,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'delete_parent_menu':
    $id=$_POST['id']??0; if(!$id) throw new Exception('Invalid ID');
    $pdo->prepare("DELETE FROM sub_menus WHERE parent_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM parent_menus WHERE id=?")->execute([$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

// ==================== SUB MENUS ====================
case 'get_sub_menus':
    echo json_encode($pdo->query("SELECT * FROM sub_menus ORDER BY parent_id,id")->fetchAll()); break;

case 'add_sub_menu':
    $name=trim($_POST['name']??''); $link=trim($_POST['link']??''); $parent=$_POST['parent']??0;
    if(!$name||!$link||!$parent) throw new Exception('All fields required');
    $pdo->prepare("INSERT INTO sub_menus(name,link,parent_id) VALUES(?,?,?)")->execute([$name,$link,$parent]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); break;

case 'update_sub_menu':
    $id=$_POST['id']??0; $name=trim($_POST['name']??''); $link=trim($_POST['link']??''); $parent=$_POST['parent']??0;
    if(!$id||!$name||!$link||!$parent) throw new Exception('Invalid data');
    $pdo->prepare("UPDATE sub_menus SET name=?,link=?,parent_id=? WHERE id=?")->execute([$name,$link,$parent,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'delete_sub_menu':
    $id=$_POST['id']??0; if(!$id) throw new Exception('Invalid ID');
    $pdo->prepare("DELETE FROM sub_menus WHERE id=?")->execute([$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

// ==================== TOOL ITEMS ====================
case 'get_tools':
    echo json_encode($pdo->query("SELECT * FROM tool_items ORDER BY position,id")->fetchAll()); break;

case 'add_tool':
    $name=trim($_POST['name']??''); $icon=trim($_POST['icon']??'🔧'); $link=trim($_POST['link']??'');
    $desc=trim($_POST['description']??''); $image=$_POST['image']??''; $parent=!empty($_POST['parent'])?$_POST['parent']:null;
    if(!$name||!$link||!$desc) throw new Exception('Name, link, description required');
    $pos=$pdo->query("SELECT COALESCE(MAX(position),-1)+1 as p FROM tool_items")->fetch()['p'];
    $pdo->prepare("INSERT INTO tool_items(name,icon,link,description,image,parent_id,position) VALUES(?,?,?,?,?,?,?)")->execute([$name,$icon,$link,$desc,$image,$parent,$pos]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); break;

case 'update_tool':
    $id=$_POST['id']??0; $name=trim($_POST['name']??''); $icon=trim($_POST['icon']??'🔧'); $link=trim($_POST['link']??'');
    $desc=trim($_POST['description']??''); $image=$_POST['image']??''; $parent=!empty($_POST['parent'])?$_POST['parent']:null;
    if(!$id||!$name||!$link||!$desc) throw new Exception('Invalid data');
    $pdo->prepare("UPDATE tool_items SET name=?,icon=?,link=?,description=?,image=?,parent_id=? WHERE id=?")->execute([$name,$icon,$link,$desc,$image,$parent,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'delete_tool':
    $id=$_POST['id']??0; if(!$id) throw new Exception('Invalid ID');
    $pdo->prepare("DELETE FROM tool_items WHERE id=?")->execute([$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'reorder_tools':
    $order=json_decode($_POST['order']??'[]',true);
    foreach($order as $i=>$id) $pdo->prepare("UPDATE tool_items SET position=? WHERE id=?")->execute([$i,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

// ==================== SETTINGS ====================
case 'get_data_version':
    // Lightweight check — returns just the version timestamp
    // index.html calls this first; if version changed, fetches fresh data
    $rows=$pdo->query("SELECT setting_value FROM site_settings WHERE setting_key='data_version'")->fetchAll();
    $v = $rows ? $rows[0]['setting_value'] : '0';
    echo json_encode(['version'=>$v]); break;

case 'get_settings':
    $rows=$pdo->query("SELECT setting_key,setting_value FROM site_settings")->fetchAll();
    $s=[]; foreach($rows as $r) $s[$r['setting_key']]=$r['setting_value'];
    echo json_encode($s); break;

case 'update_setting':
    $key=$_POST['key']??''; $val=$_POST['value']??'';
    if(!$key) throw new Exception('Key required');
    $pdo->prepare("INSERT INTO site_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key,$val,$val]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

// ==================== FOOTER ====================
case 'get_footer_sections':
    echo json_encode($pdo->query("SELECT * FROM footer_sections ORDER BY position,id")->fetchAll()); break;

case 'add_footer_section':
    $title=trim($_POST['title']??''); $content=$_POST['content']??'';
    if(!$title) throw new Exception('Title required');
    $pos=$pdo->query("SELECT COALESCE(MAX(position),-1)+1 as p FROM footer_sections")->fetch()['p'];
    $pdo->prepare("INSERT INTO footer_sections(title,content,position) VALUES(?,?,?)")->execute([$title,$content,$pos]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); break;

case 'update_footer_section':
    $id=$_POST['id']??0; $title=trim($_POST['title']??''); $content=$_POST['content']??'';
    if(!$id||!$title) throw new Exception('Invalid data');
    $pdo->prepare("UPDATE footer_sections SET title=?,content=? WHERE id=?")->execute([$title,$content,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'delete_footer_section':
    $id=$_POST['id']??0; if(!$id) throw new Exception('Invalid ID');
    $pdo->prepare("DELETE FROM footer_sections WHERE id=?")->execute([$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'reorder_footer_sections':
    $order=json_decode($_POST['order']??'[]',true);
    foreach($order as $i=>$id) $pdo->prepare("UPDATE footer_sections SET position=? WHERE id=?")->execute([$i,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

// ==================== TICKER ITEMS ====================
case 'get_ticker_items':
    echo json_encode($pdo->query("SELECT * FROM ticker_items ORDER BY position,id")->fetchAll()); break;

case 'add_ticker_item':
    $name=trim($_POST['name']??''); $icon=trim($_POST['icon']??'🔧'); $link=trim($_POST['link']??'');
    $badge=trim($_POST['badge']??''); $active=(int)($_POST['is_active']??1);
    if(!$name||!$link) throw new Exception('Name and link required');
    $pos=$pdo->query("SELECT COALESCE(MAX(position),-1)+1 as p FROM ticker_items")->fetch()['p'];
    $pdo->prepare("INSERT INTO ticker_items(name,icon,link,badge,is_active,position) VALUES(?,?,?,?,?,?)")->execute([$name,$icon,$link,$badge,$active,$pos]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); break;

case 'update_ticker_item':
    $id=$_POST['id']??0; $name=trim($_POST['name']??''); $icon=trim($_POST['icon']??'🔧');
    $link=trim($_POST['link']??''); $badge=trim($_POST['badge']??''); $active=(int)($_POST['is_active']??1);
    if(!$id||!$name||!$link) throw new Exception('Invalid data');
    $pdo->prepare("UPDATE ticker_items SET name=?,icon=?,link=?,badge=?,is_active=? WHERE id=?")->execute([$name,$icon,$link,$badge,$active,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'delete_ticker_item':
    $id=$_POST['id']??0; if(!$id) throw new Exception('Invalid ID');
    $pdo->prepare("DELETE FROM ticker_items WHERE id=?")->execute([$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'reorder_ticker_items':
    $order=json_decode($_POST['order']??'[]',true);
    foreach($order as $i=>$id) $pdo->prepare("UPDATE ticker_items SET position=? WHERE id=?")->execute([$i,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

// ==================== FLOATING CARDS ====================
case 'get_floating_cards':
    $cards=$pdo->query("SELECT * FROM floating_cards ORDER BY position,id")->fetchAll();
    foreach($cards as &$card){
        $card['links']=$pdo->prepare("SELECT * FROM floating_card_links WHERE card_id=? ORDER BY position,id");
        $card['links']->execute([$card['id']]);
        $card['links']=$card['links']->fetchAll();
    }
    echo json_encode($cards); break;

case 'add_floating_card':
    $title=trim($_POST['title']??''); $icon=trim($_POST['icon']??'🛠️');
    $cf=trim($_POST['color_from']??'#1a1a2e'); $ct=trim($_POST['color_to']??'#0f3460');
    $acc=trim($_POST['accent']??'#6c5ce7');
    if(!$title) throw new Exception('Title required');
    $pos=$pdo->query("SELECT COALESCE(MAX(position),-1)+1 as p FROM floating_cards")->fetch()['p'];
    $pdo->prepare("INSERT INTO floating_cards(title,icon,color_from,color_to,accent,position) VALUES(?,?,?,?,?,?)")->execute([$title,$icon,$cf,$ct,$acc,$pos]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); break;

case 'update_floating_card':
    $id=$_POST['id']??0; $title=trim($_POST['title']??''); $icon=trim($_POST['icon']??'🛠️');
    $cf=trim($_POST['color_from']??'#1a1a2e'); $ct=trim($_POST['color_to']??'#0f3460');
    $acc=trim($_POST['accent']??'#6c5ce7');
    if(!$id||!$title) throw new Exception('Invalid data');
    $pdo->prepare("UPDATE floating_cards SET title=?,icon=?,color_from=?,color_to=?,accent=? WHERE id=?")->execute([$title,$icon,$cf,$ct,$acc,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'delete_floating_card':
    $id=$_POST['id']??0; if(!$id) throw new Exception('Invalid ID');
    $pdo->prepare("DELETE FROM floating_card_links WHERE card_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM floating_cards WHERE id=?")->execute([$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'reorder_floating_cards':
    $order=json_decode($_POST['order']??'[]',true);
    foreach($order as $i=>$id) $pdo->prepare("UPDATE floating_cards SET position=? WHERE id=?")->execute([$i,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

// ==================== CARD LINKS ====================
case 'add_card_link':
    $cid=$_POST['card_id']??0; $name=trim($_POST['name']??''); $icon=trim($_POST['icon']??'🔗');
    $link=trim($_POST['link']??''); $badge=trim($_POST['badge']??'');
    if(!$cid||!$name||!$link) throw new Exception('card_id, name, link required');
    $pos=$pdo->prepare("SELECT COALESCE(MAX(position),-1)+1 as p FROM floating_card_links WHERE card_id=?");
    $pos->execute([$cid]); $pos=$pos->fetch()['p'];
    $pdo->prepare("INSERT INTO floating_card_links(card_id,name,icon,link,badge,position) VALUES(?,?,?,?,?,?)")->execute([$cid,$name,$icon,$link,$badge,$pos]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); break;

case 'update_card_link':
    $id=$_POST['id']??0; $name=trim($_POST['name']??''); $icon=trim($_POST['icon']??'🔗');
    $link=trim($_POST['link']??''); $badge=trim($_POST['badge']??'');
    if(!$id||!$name||!$link) throw new Exception('Invalid data');
    $pdo->prepare("UPDATE floating_card_links SET name=?,icon=?,link=?,badge=? WHERE id=?")->execute([$name,$icon,$link,$badge,$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

case 'delete_card_link':
    $id=$_POST['id']??0; if(!$id) throw new Exception('Invalid ID');
    $pdo->prepare("DELETE FROM floating_card_links WHERE id=?")->execute([$id]);
    bumpVersion($pdo);
    echo json_encode(['success'=>true]); break;

default: throw new Exception('Invalid action: '.$action);
}} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['error'=>$e->getMessage(),'success'=>false]);
}
?>
