<?php
require_once 'conexion.php';

try {
    $conn = getConnection();
    $sql = "SELECT matricula, nombre, apaterno, amaterno, fecha_nacimiento, status 
            FROM alumnos 
            WHERE status = 0
            ORDER BY matricula";
    $result = $conn->query($sql);
    $alumnos = $result->fetchAll();
} catch (PDOException $e) {
    $alumnos = [];
    $error_bd = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos Inactivos - UTCAM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Cliente.css">
    <style>
        .btn-habilitar {
            color: var(--success);
        }
        .btn-habilitar:hover {
            background: rgba(0, 255, 136, 0.15);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-brand">
            <i class="fa-solid fa-archive"></i>
            <span>Alumnos Inactivos</span>
        </div>
    </header>
    <main class="main">
        <div class="section-header">
            <div>
                <h2><i class="fa-solid fa-user-slash"></i> Alumnos Suspendidos</h2>
                <p>Registros con estatus inactivo</p>
            </div>
            <a href="cliente.php" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver a Activos
            </a>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>Fecha Nacimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($error_bd)): ?>
                        <tr><td colspan="6"><div class="empty-state">Error: <?php echo htmlspecialchars($error_bd); ?></div></td></tr>
                    <?php elseif (count($alumnos) > 0): ?>
                        <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alumno['matricula']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['apaterno']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['amaterno']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['fecha_nacimiento']); ?></td>
                            <td>
                                <div class="acciones-links">
                                    <a href="javascript:void(0)" onclick="habilitarAlumno('<?php echo $alumno['matricula']; ?>')" class="btn-link btn-habilitar">
                                        <i class="fa-solid fa-check-circle"></i> Habilitar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-check"></i> No hay alumnos inactivos</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function habilitarAlumno(matricula) {
            Swal.fire({
                title: '¿Habilitar alumno?',
                text: `El alumno con matrícula ${matricula} volverá a estar activo.`,
                icon: 'question',
                background: '#111827',
                color: '#e2e8f0',
                showCancelButton: true,
                confirmButtonColor: '#00ff88',
                confirmButtonText: 'Sí, habilitar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `habilitar_alumno.php?matricula=${matricula}`;
                }
            });
        }
    </script>
</body>
</html>