<?php
// ================================================================
//  api_auth.php — Autenticación por tokens para la API UTCAM
//  Columnas BD: id_usuario | nombre_usuario | login | password
//               api_token  | token_expira
// ================================================================
require_once 'config.php';

// ────────────────────────────────────────────────────────────────
//  Genera un token seguro y lo guarda en la BD
// ────────────────────────────────────────────────────────────────
function generarToken(int $userId): string {
    $token  = bin2hex(random_bytes(32));   // 64 chars hex
    $expira = time() + TOKEN_EXPIRY;

    $conn = getConnection();
    $stmt = $conn->prepare(
        "UPDATE usuarios 
         SET api_token = :token, token_expira = :expira 
         WHERE id_usuario = :id"
    );
    $stmt->execute([':token' => $token, ':expira' => $expira, ':id' => $userId]);

    return $token;
}

// ────────────────────────────────────────────────────────────────
//  Valida el token del header Authorization
//  Retorna array del usuario o false
// ────────────────────────────────────────────────────────────────
function validarToken(string $token): array|false {
    $conn = getConnection();
    $stmt = $conn->prepare(
        "SELECT id_usuario, nombre_usuario, login, api_token, token_expira 
         FROM usuarios 
         WHERE api_token = :token 
         LIMIT 1"
    );
    $stmt->execute([':token' => $token]);
    $usuario = $stmt->fetch();

    if (!$usuario) return false;

    if (time() > (int)$usuario['token_expira']) {
        // Token expirado: limpiar
        $conn->prepare(
            "UPDATE usuarios SET api_token = NULL, token_expira = NULL WHERE id_usuario = :id"
        )->execute([':id' => $usuario['id_usuario']]);
        return false;
    }

    return $usuario;
}

// ────────────────────────────────────────────────────────────────
//  Middleware: extrae y verifica el token del header Authorization
// ────────────────────────────────────────────────────────────────
function verificarAuth(): array {
    $headers    = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!str_starts_with($authHeader, 'Bearer ')) {
        jsonResponse(401, ['error' => 'Token no proporcionado. Incluye: Authorization: Bearer <token>']);
    }

    $token   = trim(substr($authHeader, 7));
    $usuario = validarToken($token);

    if (!$usuario) {
        jsonResponse(401, ['error' => 'Token inválido o expirado. Vuelve a iniciar sesión.']);
    }

    return $usuario;
}

// ────────────────────────────────────────────────────────────────
//  Login: valida credenciales y devuelve token
// ────────────────────────────────────────────────────────────────
function loginUsuario(string $login, string $password): array {
    if (empty($login) || empty($password)) {
        jsonResponse(400, ['error' => 'Usuario y contraseña son obligatorios.']);
    }

    $conn = getConnection();
    $stmt = $conn->prepare(
        "SELECT id_usuario, nombre_usuario, login, password 
         FROM usuarios 
         WHERE login = :login 
         LIMIT 1"
    );
    $stmt->execute([':login' => $login]);
    $usuario = $stmt->fetch();

    // Comparación: primero intenta bcrypt, luego texto plano (compatibilidad)
    $ok = false;
    if ($usuario) {
        if (password_verify($password, $usuario['password'])) {
            $ok = true;
        } elseif ($usuario['password'] === $password) {
            $ok = true;
        }
    }

    if (!$ok) {
        jsonResponse(401, ['error' => 'Usuario o contraseña incorrectos.']);
    }

    $token = generarToken((int)$usuario['id_usuario']);

    return [
        'mensaje'        => 'Login exitoso.',
        'token'          => $token,
        'expira_en'      => TOKEN_EXPIRY,
        'usuario_id'     => $usuario['id_usuario'],
        'login'          => $usuario['login'],
        'nombre_usuario' => $usuario['nombre_usuario'],
    ];
}

// ────────────────────────────────────────────────────────────────
//  Registro: crea un nuevo usuario
// ────────────────────────────────────────────────────────────────
function registrarUsuario(string $nombre, string $login, string $password): array {
    if (empty($nombre) || empty($login) || empty($password)) {
        jsonResponse(400, ['error' => 'Nombre, usuario y contraseña son obligatorios.']);
    }
    if (strlen($login) < 3) {
        jsonResponse(400, ['error' => 'El usuario debe tener al menos 3 caracteres.']);
    }
    if (strlen($password) < 6) {
        jsonResponse(400, ['error' => 'La contraseña debe tener al menos 6 caracteres.']);
    }
    if (!preg_match('/^[a-zA-Z0-9_.]+$/', $login)) {
        jsonResponse(400, ['error' => 'El usuario solo puede contener letras, números, "." y "_".']);
    }

    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE login = :login LIMIT 1");
    $stmt->execute([':login' => $login]);
    if ($stmt->fetch()) {
        jsonResponse(409, ['error' => "El usuario \"$login\" ya está registrado."]);
    }

    // Guardar contraseña con hash bcrypt
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO usuarios (nombre_usuario, login, password) 
         VALUES (:nombre, :login, :password)"
    );
    $stmt->execute([
        ':nombre'   => $nombre,
        ':login'    => $login,
        ':password' => $hashed,
    ]);

    return [
        'mensaje'        => 'Usuario registrado correctamente.',
        'usuario_id'     => (int) $conn->lastInsertId(),
        'login'          => $login,
        'nombre_usuario' => $nombre,
    ];
}

// ────────────────────────────────────────────────────────────────
//  Logout: invalida el token
// ────────────────────────────────────────────────────────────────
function logoutUsuario(array $usuario): array {
    $conn = getConnection();
    $conn->prepare(
        "UPDATE usuarios SET api_token = NULL, token_expira = NULL WHERE id_usuario = :id"
    )->execute([':id' => $usuario['id_usuario']]);

    return ['mensaje' => 'Sesión cerrada correctamente.'];
}

// ================================================================
//  PUNTO DE ENTRADA PRINCIPAL
// ================================================================
$ruta = $_GET['ruta'] ?? '';

switch ($ruta) {
    case 'login':
        $datos = json_decode(file_get_contents('php://input'), true);
        $resultado = loginUsuario($datos['login'] ?? '', $datos['password'] ?? '');
        jsonResponse(200, $resultado);
        break;

    case 'registro':
        $datos = json_decode(file_get_contents('php://input'), true);
        $resultado = registrarUsuario(
            $datos['nombre_usuario'] ?? '',
            $datos['login'] ?? '',
            $datos['password'] ?? ''
        );
        jsonResponse(201, $resultado);
        break;

    case 'logout':
        $usuario = verificarAuth();
        $resultado = logoutUsuario($usuario);
        jsonResponse(200, $resultado);
        break;

    default:
        jsonResponse(400, ['error' => 'Ruta no válida. Usa: login, registro, logout']);
        break;
}
?>