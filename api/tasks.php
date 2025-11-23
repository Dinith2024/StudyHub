<?php
// tasks.php – handles CRUD for tasks
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Allow CORS (useful when testing)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// If browser sends OPTIONS (preflight), just exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// Helper to read JSON body
function body() {
    return json_decode(file_get_contents('php://input'), true) ?? $_POST;
}

$method = $_SERVER['REQUEST_METHOD'];

//READ (GET all tasks)
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

//CREATE (POST new task)
if ($method === 'POST') {
    $data = body();
    $title = trim($data['title'] ?? '');
    if ($title === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Title required']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, category, status) 
                           VALUES (:title, :desc, :due, :cat, :status)");
    $stmt->execute([
        ':title' => $title,
        ':desc' => $data['description'] ?? '',
        ':due' => $data['due_date'] ?? null,
        ':cat' => $data['category'] ?? 'General',
        ':status' => $data['status'] ?? 'pending'
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
    foreach (['title','description','due_date','status','category'] as $f) {
        if (isset($data[$f])) {
            $fields[] = "$f = :$f";
            $params[":$f"] = $data[$f];
        }
    }
    if (!$fields) { http_response_code(400); echo json_encode(['error'=>'Nothing to update']); exit; }
    $sql = "UPDATE tasks SET " . implode(',', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['ok' => true]);
    exit;
}

//DELETE
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID required']); exit; }
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['ok' => true]);
    exit;
}

// If method not supported
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
