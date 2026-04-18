<?php
// ================================================================
//  servidor.php — API REST para gestión de alumnos (con token)
//  Endpoints:
//    GET    ?ruta=alumnos         -> Lista activos
//    GET    ?ruta=alumnos_inactivos -> Lista inactivos
//    POST   ?ruta=alumnos         -> Crear alumno
//    PUT    ?ruta=alumnos&id=XXX  -> Actualizar alumno
//    DELETE ?ruta=alumnos&id=XXX  -> Suspender (status=0)
//    PUT    ?ruta=habilitar&id=XXX -> Habilitar (status=1)
// ================================================================
require_once 'config.php';
require_once 'api_auth.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar token para todas las rutas excepto login/registro/logout
$ruta = $_GET['ruta'] ?? '';
$rutas_publicas = ['login', 'registro', 'logout'];

if (!in_array($ruta, $rutas_publicas)) {
    verificarAuth();  // Valida el token
}

$conn = getConnection();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($ruta) {
    // ============================================================
    case 'alumnos':
        switch ($metodo) {
            case 'GET':
                // Listar alumnos activos (status = 1)
                $stmt = $conn->prepare("SELECT * FROM alumnos WHERE status = 1 ORDER BY matricula");
                $stmt->execute();
                jsonResponse(200, ['alumnos' => $stmt->fetchAll()]);
                break;

            case 'POST':
                // Crear alumno
                $datos = json_decode(file_get_contents('php://input'), true);
                $errores = validarAlumno($datos);
                if (!empty($errores)) {
                    jsonResponse(400, ['error' => $errores]);
                }
                
                // Verificar que la matrícula no exista
                $stmt = $conn->prepare("SELECT matricula FROM alumnos WHERE matricula = ?");
                $stmt->execute([$datos['matricula']]);
                if ($stmt->fetch()) {
                    jsonResponse(409, ['error' => 'Ya existe un alumno con esa matrícula.']);
                }
                
                $stmt = $conn->prepare(
                    "INSERT INTO alumnos (matricula, nombre, apaterno, amaterno, fecha_nacimiento, status) 
                     VALUES (?, ?, ?, ?, ?, 1)"
                );
                $stmt->execute([
                    $datos['matricula'],
                    $datos['nombre'],
                    $datos['apaterno'],
                    $datos['amaterno'],
                    $datos['fecha_nacimiento']
                ]);
                jsonResponse(201, ['mensaje' => 'Alumno creado con éxito', 'matricula' => $datos['matricula']]);
                break;

            case 'PUT':
                // Actualizar alumno (no se puede cambiar matrícula)
                $matricula = $_GET['id'] ?? '';
                if (empty($matricula)) {
                    jsonResponse(400, ['error' => 'ID (matrícula) requerido']);
                }
                
                $datos = json_decode(file_get_contents('php://input'), true);
                $errores = validarAlumno($datos, false);
                if (!empty($errores)) {
                    jsonResponse(400, ['error' => $errores]);
                }
                
                $stmt = $conn->prepare(
                    "UPDATE alumnos SET nombre=?, apaterno=?, amaterno=?, fecha_nacimiento=?, status=1 
                     WHERE matricula = ?"
                );
                $stmt->execute([
                    $datos['nombre'],
                    $datos['apaterno'],
                    $datos['amaterno'],
                    $datos['fecha_nacimiento'],
                    $matricula
                ]);
                
                if ($stmt->rowCount() === 0) {
                    jsonResponse(404, ['error' => 'Alumno no encontrado']);
                }
                jsonResponse(200, ['mensaje' => 'Alumno actualizado con éxito']);
                break;

            case 'DELETE':
                // Suspender (baja lógica)
                $matricula = $_GET['id'] ?? '';
                if (empty($matricula)) {
                    jsonResponse(400, ['error' => 'ID (matrícula) requerido']);
                }
                
                $stmt = $conn->prepare("UPDATE alumnos SET status = 0 WHERE matricula = ?");
                $stmt->execute([$matricula]);
                
                if ($stmt->rowCount() === 0) {
                    jsonResponse(404, ['error' => 'Alumno no encontrado']);
                }
                jsonResponse(200, ['mensaje' => 'Alumno suspendido correctamente']);
                break;
        }
        break;

    // ============================================================
    case 'alumnos_inactivos':
        if ($metodo !== 'GET') {
            jsonResponse(405, ['error' => 'Método no permitido']);
        }
        $stmt = $conn->prepare("SELECT * FROM alumnos WHERE status = 0 ORDER BY matricula");
        $stmt->execute();
        jsonResponse(200, ['alumnos' => $stmt->fetchAll()]);
        break;

    // ============================================================
    case 'habilitar':
        if ($metodo !== 'PUT') {
            jsonResponse(405, ['error' => 'Método no permitido']);
        }
        $matricula = $_GET['id'] ?? '';
        if (empty($matricula)) {
            jsonResponse(400, ['error' => 'ID (matrícula) requerido']);
        }
        
        $stmt = $conn->prepare("UPDATE alumnos SET status = 1 WHERE matricula = ?");
        $stmt->execute([$matricula]);
        
        if ($stmt->rowCount() === 0) {
            jsonResponse(404, ['error' => 'Alumno no encontrado']);
        }
        jsonResponse(200, ['mensaje' => 'Alumno habilitado correctamente']);
        break;

    // ============================================================
    // Las rutas login, registro, logout se manejan en api_auth.php
    // Pero por si llegan aquí, redirigimos
    case 'login':
    case 'registro':
    case 'logout':
        jsonResponse(400, ['error' => 'Esta ruta debe ser manejada por api_auth.php']);
        break;

    // ============================================================
    default:
        jsonResponse(400, ['error' => 'Ruta no válida. Opciones: alumnos, alumnos_inactivos, habilitar']);
        break;
}

// ================================================================
//  FUNCIÓN DE VALIDACIÓN
// ================================================================
function validarAlumno($datos, $requiereMatricula = true): array {
    $errores = [];
    
    if ($requiereMatricula && (empty($datos['matricula']) || strlen($datos['matricula']) < 3)) {
        $errores[] = 'Matrícula requerida (mínimo 3 caracteres)';
    }
    if (empty($datos['nombre'])) {
        $errores[] = 'Nombre es requerido';
    }
    if (empty($datos['apaterno'])) {
        $errores[] = 'Apellido paterno es requerido';
    }
    if (empty($datos['amaterno'])) {
        $errores[] = 'Apellido materno es requerido';
    }
    if (empty($datos['fecha_nacimiento'])) {
        $errores[] = 'Fecha de nacimiento es requerida';
    }
    
    return $errores;
}
?>