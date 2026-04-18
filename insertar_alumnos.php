<?php
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("INSERT INTO alumnos (matricula, nombre, apaterno, amaterno, fecha_nacimiento, status) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([
            $_POST['matricula'],
            $_POST['nombre'],
            $_POST['apaterno'],
            $_POST['amaterno'],
            $_POST['fecha_nacimiento'],
            $_POST['status'] ?? 1
        ])) {
            header("Location: cliente.php?mensaje=success");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Alumno - UTCAM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Cliente.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--neon-cyan);
            font-weight: 600;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 10px rgba(0,212,255,0.3);
        }
        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-brand">
            <i class="fa-solid fa-user-plus"></i>
            <span>Registro de Alumnos</span>
        </div>
    </header>
    <main class="main">
        <div class="form-container">
            <div class="card" style="padding: 2rem;">
                <h2 style="margin-bottom: 1.5rem; text-align: center;">📝 Nuevo Alumno</h2>
                <?php if (isset($error)): ?>
                    <div style="background: rgba(255,51,102,0.2); border: 1px solid var(--danger); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fa-solid fa-id-card"></i> Matrícula</label>
                        <input type="text" name="matricula" required placeholder="Ej: A23001">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-user"></i> Nombre(s)</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-user"></i> Apellido Paterno</label>
                        <input type="text" name="apaterno" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-user"></i> Apellido Materno</label>
                        <input type="text" name="amaterno" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-calendar"></i> Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-toggle-on"></i> Estatus</label>
                        <select name="status">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="fa-solid fa-save"></i> Guardar Alumno
                    </button>
                    <a href="cliente.php" class="btn btn-secondary" style="display: block; text-align: center; margin-top: 1rem;">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                </form>
            </div>
        </div>
    </main>
</body>
</html>