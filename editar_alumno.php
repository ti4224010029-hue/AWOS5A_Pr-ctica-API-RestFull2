<?php
require_once 'conexion.php';

$pdo = getConnection();

if (!isset($_GET['matricula'])) {
    header("Location: cliente.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE matricula = ?");
$stmt->execute([$_GET['matricula']]);
$alumno = $stmt->fetch();

if (!$alumno) {
    header("Location: cliente.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "UPDATE alumnos SET nombre=?, apaterno=?, amaterno=?, fecha_nacimiento=?, status=? WHERE matricula=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nombre'],
            $_POST['apaterno'],
            $_POST['amaterno'],
            $_POST['fecha_nacimiento'],
            $_POST['status'],
            $_POST['matricula']
        ]);
        header("Location: cliente.php?mensaje=edited");
        exit();
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Alumno - UTCAM</title>
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
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .btn-group .btn {
            flex: 1;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-brand">
            <i class="fa-solid fa-pen"></i>
            <span>Editar Alumno</span>
        </div>
    </header>
    <main class="main">
        <div class="form-container">
            <div class="card" style="padding: 2rem;">
                <h2 style="margin-bottom: 1.5rem;">✏️ Editar: <?php echo htmlspecialchars($alumno['matricula']); ?></h2>
                <?php if (isset($error)): ?>
                    <div style="background: rgba(255,51,102,0.2); border: 1px solid var(--danger); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="matricula" value="<?php echo $alumno['matricula']; ?>">
                    <div class="form-group">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($alumno['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apaterno" value="<?php echo htmlspecialchars($alumno['apaterno']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido Materno</label>
                        <input type="text" name="amaterno" value="<?php echo htmlspecialchars($alumno['amaterno']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" value="<?php echo $alumno['fecha_nacimiento']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Estatus</label>
                        <select name="status">
                            <option value="1" <?php echo $alumno['status'] == 1 ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo $alumno['status'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="btn-group">
                        <a href="cliente.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>