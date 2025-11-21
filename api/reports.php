<?php
/**
 * Reports API - CRUD Operations
 * Handles all Create, Read, Update, Delete operations for reports
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get database connection
$conn = getDBConnection();

try {
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
            
        case 'POST':
            handlePost($conn);
            break;
            
        case 'PUT':
            handlePut($conn);
            break;
            
        case 'DELETE':
            handleDelete($conn);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    closeDBConnection($conn);
}

/**
 * Handle GET requests - Read operations
 */
function handleGet($conn) {
    // Get report by ID
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM reports WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $report]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Report not found']);
        }
        $stmt->close();
        return;
    }
    
    // Get all reports with optional filtering
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $query = "SELECT * FROM reports";
    
    if ($status && $status !== 'All Reports') {
        $query .= " WHERE status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = [];
    
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $reports]);
    $stmt->close();
}

/**
 * Handle POST requests - Create operations
 */
function handlePost($conn) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['student']) || !isset($input['type']) || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields: student, type, and content are required']);
        return;
    }
    
    $student = trim($input['student']);
    $quiz = isset($input['quiz']) ? trim($input['quiz']) : null;
    $type = trim($input['type']);
    $status = isset($input['status']) ? trim($input['status']) : 'Pending';
    $content = trim($input['content']);
    $createdDate = isset($input['created_date']) ? $input['created_date'] : date('Y-m-d H:i:s');
    
    // Insert new report
    $stmt = $conn->prepare("INSERT INTO reports (student, quiz, type, status, content, created_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $student, $quiz, $type, $status, $content, $createdDate);
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Report created successfully',
            'data' => ['id' => $newId]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create report: ' . $stmt->error]);
    }
    
    $stmt->close();
}

/**
 * Handle PUT requests - Update operations
 */
function handlePut($conn) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Report ID is required']);
        return;
    }
    
    $id = intval($input['id']);
    
    // Build update query dynamically based on provided fields
    $updates = [];
    $params = [];
    $types = '';
    
    if (isset($input['student'])) {
        $updates[] = "student = ?";
        $params[] = trim($input['student']);
        $types .= 's';
    }
    
    if (isset($input['quiz'])) {
        $updates[] = "quiz = ?";
        $params[] = trim($input['quiz']);
        $types .= 's';
    }
    
    if (isset($input['type'])) {
        $updates[] = "type = ?";
        $params[] = trim($input['type']);
        $types .= 's';
    }
    
    if (isset($input['status'])) {
        $updates[] = "status = ?";
        $params[] = trim($input['status']);
        $types .= 's';
    }
    
    if (isset($input['content'])) {
        $updates[] = "content = ?";
        $params[] = trim($input['content']);
        $types .= 's';
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        return;
    }
    
    $updates[] = "updated_date = NOW()";
    $query = "UPDATE reports SET " . implode(", ", $updates) . " WHERE id = ?";
    $params[] = $id;
    $types .= 'i';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Report updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Report not found or no changes made']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update report: ' . $stmt->error]);
    }
    
    $stmt->close();
}

/**
 * Handle DELETE requests - Delete operations
 */
function handleDelete($conn) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Report ID is required']);
        return;
    }
    
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Report not found']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete report: ' . $stmt->error]);
    }
    
    $stmt->close();
}

