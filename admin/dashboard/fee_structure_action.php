<?php
// Include database connection
include 'con.php';

// Get action from request
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Response array
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

// Handle different actions
switch ($action) {
    case 'get_academic_years':
        // Get all academic years
        $sql = "SELECT id, name FROM academic_years ORDER BY start_date DESC";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $years = [];
            while ($row = $result->fetch_assoc()) {
                $years[] = $row;
            }
            
            $response = [
                'success' => true,
                'years' => $years
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No academic years found'
            ];
        }
        break;
    
    case 'get_classes':
        // Get all classes
        $sql = "SELECT id, name FROM classes ORDER BY name";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $classes = [];
            while ($row = $result->fetch_assoc()) {
                $classes[] = $row;
            }
            
            $response = [
                'success' => true,
                'classes' => $classes
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No classes found'
            ];
        }
        break;
    
    case 'get_structures':
        // Get fee structures with optional filters
        $conditions = [];
        $params = [];
        $types = '';
        
        if (isset($_REQUEST['academic_year_id']) && !empty($_REQUEST['academic_year_id'])) {
            $conditions[] = "fs.academic_year_id = ?";
            $params[] = $_REQUEST['academic_year_id'];
            $types .= 'i';
        }
        
        if (isset($_REQUEST['class_id']) && !empty($_REQUEST['class_id'])) {
            $conditions[] = "fs.class_id = ?";
            $params[] = $_REQUEST['class_id'];
            $types .= 'i';
        }
        
        $whereClause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";
        
        $sql = "SELECT fs.*, ay.name as academic_year_name, c.name as class_name 
                FROM fee_structures fs
                LEFT JOIN academic_years ay ON fs.academic_year_id = ay.id
                LEFT JOIN classes c ON fs.class_id = c.id
                $whereClause
                ORDER BY fs.due_date DESC";
        
        $stmt = $conn->prepare($sql);
        
        if (count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $structures = [];
            while ($row = $result->fetch_assoc()) {
                // Get fee components for this structure
                $componentsStmt = $conn->prepare("SELECT name, amount FROM fee_components WHERE fee_structure_id = ?");
                $componentsStmt->bind_param('i', $row['id']);
                $componentsStmt->execute();
                $componentsResult = $componentsStmt->get_result();
                
                $components = [];
                if ($componentsResult && $componentsResult->num_rows > 0) {
                    while ($component = $componentsResult->fetch_assoc()) {
                        $components[] = $component;
                    }
                }
                
                $row['components'] = $components;
                $structures[] = $row;
            }
            
            $response = [
                'success' => true,
                'structures' => $structures
            ];
        } else {
            $response = [
                'success' => true,
                'structures' => [],
                'message' => 'No fee structures found'
            ];
        }
        break;
    
    case 'get_structure':
        // Get a specific fee structure by ID
        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            
            $sql = "SELECT fs.*, ay.name as academic_year_name, c.name as class_name 
                    FROM fee_structures fs
                    LEFT JOIN academic_years ay ON fs.academic_year_id = ay.id
                    LEFT JOIN classes c ON fs.class_id = c.id
                    WHERE fs.id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $structure = $result->fetch_assoc();
                
                // Get fee components for this structure
                $componentsStmt = $conn->prepare("SELECT name, amount FROM fee_components WHERE fee_structure_id = ?");
                $componentsStmt->bind_param('i', $id);
                $componentsStmt->execute();
                $componentsResult = $componentsStmt->get_result();
                
                $components = [];
                if ($componentsResult && $componentsResult->num_rows > 0) {
                    while ($component = $componentsResult->fetch_assoc()) {
                        $components[] = $component;
                    }
                }
                
                $structure['components'] = $components;
                
                $response = [
                    'success' => true,
                    'structure' => $structure
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Fee structure not found'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Missing structure ID'
            ];
        }
        break;
    
    case 'add_structure':
        // Add a new fee structure
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Get form data
                $academicYear = $_POST['academicYearSelect'];
                $class = $_POST['classSelect'];
                $title = $_POST['feeTitle'];
                $amount = $_POST['totalAmount'];
                $dueDate = $_POST['dueDate'];
                $description = $_POST['description'] ?? '';
                
                // Insert fee structure
                $sql = "INSERT INTO fee_structures (academic_year_id, class_id, title, amount, due_date, description) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('iisdss', $academicYear, $class, $title, $amount, $dueDate, $description);
                $stmt->execute();
                
                $structureId = $conn->insert_id;
                
                // Insert fee components
                if (isset($_POST['componentName']) && is_array($_POST['componentName']) && 
                    isset($_POST['componentAmount']) && is_array($_POST['componentAmount'])) {
                    
                    $componentSql = "INSERT INTO fee_components (fee_structure_id, name, amount) VALUES (?, ?, ?)";
                    $componentStmt = $conn->prepare($componentSql);
                    
                    for ($i = 0; $i < count($_POST['componentName']); $i++) {
                        $componentName = $_POST['componentName'][$i];
                        $componentAmount = $_POST['componentAmount'][$i];
                        
                        if (!empty($componentName) && $componentAmount !== '') {
                            $componentStmt->bind_param('isd', $structureId, $componentName, $componentAmount);
                            $componentStmt->execute();
                        }
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Fee structure added successfully'
                ];
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                
                $response = [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid request method'
            ];
        }
        break;
    
    case 'update_structure':
        // Update an existing fee structure
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['structureId']) && !empty($_POST['structureId'])) {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Get form data
                $structureId = $_POST['structureId'];
                $academicYear = $_POST['academicYearSelect'];
                $class = $_POST['classSelect'];
                $title = $_POST['feeTitle'];
                $amount = $_POST['totalAmount'];
                $dueDate = $_POST['dueDate'];
                $description = $_POST['description'] ?? '';
                
                // Update fee structure
                $sql = "UPDATE fee_structures 
                        SET academic_year_id = ?, class_id = ?, title = ?, amount = ?, due_date = ?, description = ? 
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('iisdssi', $academicYear, $class, $title, $amount, $dueDate, $description, $structureId);
                $stmt->execute();
                
                // Delete existing components
                $deleteSql = "DELETE FROM fee_components WHERE fee_structure_id = ?";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param('i', $structureId);
                $deleteStmt->execute();
                
                // Insert new fee components
                if (isset($_POST['componentName']) && is_array($_POST['componentName']) && 
                    isset($_POST['componentAmount']) && is_array($_POST['componentAmount'])) {
                    
                    $componentSql = "INSERT INTO fee_components (fee_structure_id, name, amount) VALUES (?, ?, ?)";
                    $componentStmt = $conn->prepare($componentSql);
                    
                    for ($i = 0; $i < count($_POST['componentName']); $i++) {
                        $componentName = $_POST['componentName'][$i];
                        $componentAmount = $_POST['componentAmount'][$i];
                        
                        if (!empty($componentName) && $componentAmount !== '') {
                            $componentStmt->bind_param('isd', $structureId, $componentName, $componentAmount);
                            $componentStmt->execute();
                        }
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Fee structure updated successfully'
                ];
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                
                $response = [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid request or missing structure ID'
            ];
        }
        break;
    
    case 'delete_structure':
        // Delete a fee structure
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
            $structureId = $_POST['id'];
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // First delete components
                $deleteComponentsSql = "DELETE FROM fee_components WHERE fee_structure_id = ?";
                $deleteComponentsStmt = $conn->prepare($deleteComponentsSql);
                $deleteComponentsStmt->bind_param('i', $structureId);
                $deleteComponentsStmt->execute();
                
                // Then delete structure
                $deleteStructureSql = "DELETE FROM fee_structures WHERE id = ?";
                $deleteStructureStmt = $conn->prepare($deleteStructureSql);
                $deleteStructureStmt->bind_param('i', $structureId);
                $deleteStructureStmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Fee structure deleted successfully'
                ];
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                
                $response = [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid request or missing structure ID'
            ];
        }
        break;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 