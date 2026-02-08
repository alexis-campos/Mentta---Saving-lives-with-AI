<?php
/**
 * API: Generate Psychologist Code
 * Generates a text code for patient linking
 */

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

// 1. Verify Authentication & Role
$user = requireAuth('psychologist'); // Helper function throws 401/403 if invalid

try {
    $db = getDB();
    $userId = $user['id'];

    // 2. Check for existing valid code
    $stmt = $db->prepare("
        SELECT code, expires_at 
        FROM psychologist_codes 
        WHERE psychologist_id = ? AND expires_at > NOW() 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode([
            'success' => true,
            'code' => $existing['code'],
            'expires_at' => $existing['expires_at'],
            'is_new' => false
        ]);
        exit;
    }

    // 3. Generate new unique code
    $code = '';
    $attempts = 0;
    $maxAttempts = 5;

    do {
        // Generate 6 random uppercase alphanumeric chars
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }

        // Check uniqueness
        $check = $db->prepare("SELECT id FROM psychologist_codes WHERE code = ?");
        $check->execute([$code]);
        if (!$check->fetch()) {
            break;
        }
        $attempts++;
    } while ($attempts < $maxAttempts);

    if ($attempts >= $maxAttempts) {
        throw new Exception("Unable to generate unique code. Please try again.");
    }

    // 4. Save to DB (Delete old/expired codes for this user first to keep table clean-ish)
    // Optional: Keep history if needed, but for now we wipe old ones for this user or just insert new
    // Let's just insert new, we can run a cleanup job separately or just ignore old ones
    
    // Calculate expiration
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $insert = $db->prepare("
        INSERT INTO psychologist_codes (psychologist_id, code, expires_at)
        VALUES (?, ?, ?)
    ");
    $insert->execute([$userId, $code, $expiresAt]);

    echo json_encode([
        'success' => true,
        'code' => $code,
        'expires_at' => $expiresAt,
        'is_new' => true
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
