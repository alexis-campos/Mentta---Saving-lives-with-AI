<?php
/**
 * API: Link Psychologist
 * Links a patient to a psychologist using a code
 */

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

// 1. Verify Authentication & Role
$user = requireAuth('patient'); 

// 2. Get Input
$input = json_decode(file_get_contents('php://input'), true);
$code = isset($input['code']) ? strtoupper(trim($input['code'])) : '';

if (empty($code) || strlen($code) !== 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Código inválido']);
    exit;
}

try {
    $db = getDB();
    $patientId = $user['id'];

    // 3. Find Code and Validate
    $stmt = $db->prepare("
        SELECT psychologist_id, expires_at 
        FROM psychologist_codes 
        WHERE code = ?
    ");
    $stmt->execute([$code]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Código no encontrado']);
        exit;
    }

    if (strtotime($result['expires_at']) < time()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El código ha expirado']);
        exit;
    }

    $psychologistId = $result['psychologist_id'];

    // 4. Check if already linked
    $checkLink = $db->prepare("
        SELECT id FROM patient_psychologist_link 
        WHERE patient_id = ? AND psychologist_id = ?
    ");
    $checkLink->execute([$patientId, $psychologistId]);
    if ($checkLink->fetch()) {
         echo json_encode(['success' => true, 'message' => 'Ya estás vinculado con este psicólogo']);
         exit;
    }

    // 5. Check if patient has *another* active link? 
    // Logic: Usually a patient has one main psychologist. 
    // If we want to allow replacing, we should deactivate others.
    // For now, let's deactivate any existing active links for this patient to ensure 1-1 relationship if that's the business rule.
    // Assuming 1 active psychologist at a time per patient:
    $db->beginTransaction();

    $deactivate = $db->prepare("
        UPDATE patient_psychologist_link 
        SET status = 'inactive', unlinked_at = NOW() 
        WHERE patient_id = ? AND status = 'active'
    ");
    $deactivate->execute([$patientId]);

    // 6. Create Link
    $link = $db->prepare("
        INSERT INTO patient_psychologist_link (patient_id, psychologist_id, status, linked_at)
        VALUES (?, ?, 'active', NOW())
    ");
    $link->execute([$patientId, $psychologistId]);

    $db->commit();

    // 7. Get Psychologist Name for confirmation
    $psychStmt = $db->prepare("SELECT name FROM users WHERE id = ?");
    $psychStmt->execute([$psychologistId]);
    $psychName = $psychStmt->fetchColumn();

    echo json_encode([
        'success' => true, 
        'psychologist_name' => $psychName,
        'message' => 'Vinculación exitosa'
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
