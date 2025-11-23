<?php
// notes.php – handles CRUD for notes
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

function body() {
    return json_decode(file_get_contents('php://input'), true) ?? $_POST;
}

$method = $_SERVER['REQUEST_METHOD'];

//READ (GET all notes)
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM notes ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

//CREATE (POST new note)
if ($method === 'POST') {
    $data = body();
    $title = trim($data['title'] ?? '');
    $content = trim($data['content'] ?? '');
    if ($title === '' || $content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Title and content required']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO notes (title, content, tags) VALUES (:title, :content, :tags)");
    $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':tags' => $data['tags'] ?? ''
    ]);
    echo json_encode(['id' => $pdo->lastInsertId()]);
    exit;
}

//UPDATE (PUT)
if ($method === 'PUT') {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID required']); exit; }
    $data = body();
    $fields = [];
    $params = [':id' => $id];
    foreach (['title','content','tags'] as $f) {
        if (isset($data[$f])) {
            $fields[] = "$f = :$f";
            $params[":$f"] = $data[$f];
        }
    }
    if (!$fields) { http_response_code(400); echo json_encode(['error'=>'Nothing to update']); exit; }
    $sql = "UPDATE notes SET " . implode(',', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['ok' => true]);
    exit;
}

//DELETE
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID required']); exit; }
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
