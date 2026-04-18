<?php
// ================================================================
//  config.php — Configuración global del sistema UTCAM
// ================================================================

// ── Base de datos ──────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'utcam');
define('DB_USER',    'root');
define('DB_PASS',    '');           // Cambia si tienes contraseña
define('DB_CHARSET', 'utf8mb4');

// ── Seguridad ──────────────────────────────────────────────────
define('SECRET_KEY',   'UTCAM_2024_$3cr3t0!');   // Cámbialo en producción
define('TOKEN_EXPIRY', 3600 * 8);                 // 8 horas en segundos

// ── URL base ───────────────────────────────────────────────────
define('BASE_URL', 'http://localhost/Zabala/Api2');

// ── Función de conexión PDO ────────────────────────────────────
function getConnection(): PDO {
    static $conn = null;
    if ($conn === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $conn = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error de conexión a la base de datos.']);
            exit();
        }
    }
    return $conn;
}

// ── Respuesta JSON estándar ────────────────────────────────────
function jsonResponse(int $code, array $data): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// ── Helper para str_starts_with (PHP < 8.0) ────────────────────
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

// ================================================================
//  SQL DE CONFIGURACIÓN — Ejecutar UNA SOLA VEZ en phpMyAdmin
// ================================================================
//
//  -- Tabla usuarios (con columnas de token)
//  ALTER TABLE `usuarios`
//    ADD COLUMN IF NOT EXISTS `api_token`    VARCHAR(64)  DEFAULT NULL,
//    ADD COLUMN IF NOT EXISTS `token_expira` INT UNSIGNED DEFAULT NULL;
//
//  -- Tabla alumnos
//  CREATE TABLE IF NOT EXISTS `alumnos` (
//      `matricula`        VARCHAR(20)  PRIMARY KEY,
//      `nombre`           VARCHAR(80)  NOT NULL,
//      `apaterno`         VARCHAR(80)  NOT NULL,
//      `amaterno`         VARCHAR(80)  NOT NULL,
//      `fecha_nacimiento` DATE         NOT NULL,
//      `status`           TINYINT(1)   DEFAULT 1
//  );