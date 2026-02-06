<?php
/**
 * MENTTA - PHPUnit Tests
 * QA-001: Basic test structure for the Mentta application
 * 
 * Run with: ./vendor/bin/phpunit tests/MenttaTest.php
 */

use PHPUnit\Framework\TestCase;

class MenttaTest extends TestCase
{
    /**
     * Test that configs are properly loaded
     */
    public function testConfigsAreLoaded(): void
    {
        // Load config
        require_once __DIR__ . '/../includes/config.php';

        // Check required constants exist
        $this->assertTrue(defined('APP_NAME'));
        $this->assertTrue(defined('APP_VERSION'));
        $this->assertTrue(defined('SESSION_LIFETIME'));
        $this->assertTrue(defined('RATE_LIMIT_MESSAGES'));
        $this->assertTrue(defined('AI_API_KEY'));
    }

    /**
     * Test database connection
     */
    public function testDatabaseConnection(): void
    {
        require_once __DIR__ . '/../includes/db.php';

        $db = Database::getInstance();
        $this->assertNotNull($db);

        $connection = $db->getConnection();
        $this->assertInstanceOf(PDO::class, $connection);
    }

    /**
     * Test sanitization functions
     */
    public function testSanitizeInput(): void
    {
        require_once __DIR__ . '/../includes/functions.php';

        // Test XSS prevention
        $maliciousInput = '<script>alert("XSS")</script>';
        $sanitized = sanitizeInput($maliciousInput);
        $this->assertStringNotContainsString('<script>', $sanitized);

        // Test normal input passthrough
        $normalInput = 'Hello World';
        $this->assertEquals('Hello World', sanitizeInput($normalInput));
    }

    /**
     * Test rate limiting
     */
    public function testRateLimiting(): void
    {
        require_once __DIR__ . '/../includes/functions.php';

        // First request should pass
        $result = checkRateLimit(9999999, 'test_action', 5, 60);
        $this->assertTrue($result);
    }

    /**
     * Test timeAgo function
     */
    public function testTimeAgoFunction(): void
    {
        require_once __DIR__ . '/../includes/functions.php';

        // Test "just now"
        $now = date('Y-m-d H:i:s');
        $result = timeAgo($now);
        $this->assertStringContainsString('momento', $result);

        // Test yesterday
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
        $result = timeAgo($yesterday);
        $this->assertStringContainsString('Ayer', $result);
    }

    /**
     * Test password validation
     */
    public function testPasswordValidation(): void
    {
        require_once __DIR__ . '/../includes/functions.php';

        // Weak password should fail
        $weakPassword = '123';
        $this->assertFalse(strlen($weakPassword) >= PASSWORD_MIN_LENGTH);

        // Strong password should pass
        $strongPassword = 'SecurePass123!';
        $this->assertTrue(strlen($strongPassword) >= PASSWORD_MIN_LENGTH);
    }

    /**
     * Test risk level mapping
     */
    public function testRiskLevelMapping(): void
    {
        require_once __DIR__ . '/../includes/risk-detector.php';

        // Test that risk level function exists and returns expected values
        $this->assertEquals('none', riskLevelToString(0));
        $this->assertEquals('low', riskLevelToString(1));
        $this->assertEquals('medium', riskLevelToString(3));
        $this->assertEquals('high', riskLevelToString(4));
        $this->assertEquals('critical', riskLevelToString(5));
    }

    /**
     * Test circuit breaker state
     */
    public function testCircuitBreakerState(): void
    {
        require_once __DIR__ . '/../includes/circuit-breaker.php';

        $cb = new CircuitBreaker();
        $status = $cb->getStatus();

        $this->assertArrayHasKey('state', $status);
        $this->assertArrayHasKey('failures', $status);
        $this->assertArrayHasKey('storage', $status);
    }

    /**
     * Test translation function
     */
    public function testTranslations(): void
    {
        require_once __DIR__ . '/../includes/functions.php';

        // Test Spanish translation
        $this->assertEquals('Bienvenido', __('welcome', 'es'));

        // Test English translation
        $this->assertEquals('Welcome', __('welcome', 'en'));

        // Test fallback for unknown key
        $this->assertEquals('unknown_key', __('unknown_key'));
    }
}
