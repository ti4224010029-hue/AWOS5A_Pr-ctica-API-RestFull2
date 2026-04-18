<?php
// ============================================
// cliente.php - Listado principal de alumnos
// ============================================
require_once 'conexion.php';

// Consultar alumnos ACTIVOS
try {
    $conn = getConnection();
    $sql = "SELECT matricula, nombre, apaterno, amaterno, fecha_nacimiento, status 
            FROM alumnos 
            WHERE status = 1
            ORDER BY matricula";
    $result = $conn->query($sql);
    $alumnos_activos = $result->fetchAll();
} catch (PDOException $e) {
    $alumnos_activos = [];
    $error_bd = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos — UTCAM</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Cliente.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">
        <i class="fa-solid fa-microchip"></i>
        <span>UTCAM</span>
    </div>
</header>

<main class="main">
    <div class="section-header">
        <div>
            <h2><i class="fa-solid fa-users"></i> Alumnos Activos</h2>
            <p>Gestión del padrón estudiantil</p>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="count-badge">
                <i class="fa-solid fa-user-graduate"></i> <?php echo count($alumnos_activos); ?> activo<?php echo count($alumnos_activos) != 1 ? 's' : ''; ?>
            </span>
            <a href="insertar_alumnos.php" class="btn btn-primary">
                <i class="fa-solid fa-plus-circle"></i> Nuevo Alumno
            </a>
            <a href="alumnos_inactivos.php" class="btn btn-secondary">
                <i class="fa-solid fa-archive"></i> Inactivos
            </a>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Matrícula</th>
                    <th>Nombre Completo</th>
                    <th>Fecha Nacimiento</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($error_bd)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fa-solid fa-database"></i>
                                <p>⚠️ Error: <?php echo htmlspecialchars($error_bd); ?></p>
                            </div>
                        </td>
                    </tr>
                <?php elseif (count($alumnos_activos) === 0): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fa-solid fa-users-slash"></i>
                                <p>No hay alumnos activos registrados.</p>
                                <a href="insertar_alumnos.php" class="btn btn-primary" style="margin-top: 1rem;">+ Registrar primero</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($alumnos_activos as $alumno): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alumno['matricula']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apaterno'] . ' ' . $alumno['amaterno']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['fecha_nacimiento']); ?></td>
                            <td>
                                <span class="badge badge-active">
                                    <i class="fa-solid fa-circle" style="font-size:0.5rem"></i> ACTIVO
                                </span>
                            </td>
                            <td>
                                <div class="acciones-links">
                                    <a href="editar_alumno.php?matricula=<?php echo urlencode($alumno['matricula']); ?>" class="btn-link btn-edit">
                                        <i class="fa-solid fa-pen"></i> Editar
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmarSuspension('<?php echo $alumno['matricula']; ?>')" class="btn-link btn-suspend">
                                        <i class="fa-solid fa-user-slash"></i> Suspender
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    function confirmarSuspension(matricula) {
        Swal.fire({
            title: '¿Suspender alumno?',
            text: `El alumno con matrícula ${matricula} quedará como inactivo.`,
            icon: 'warning',
            background: '#111827',
            color: '#e2e8f0',
            showCancelButton: true,
            confirmButtonColor: '#ff3366',
            confirmButtonText: 'Sí, suspender',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_alumno.php?matricula=${matricula}`;
            }
        });
    }

    // Mostrar mensajes de éxito
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const mensaje = urlParams.get('mensaje');
        
        if (mensaje) {
            let title = '', icon = '';
            if (mensaje === 'success') { title = '¡Alumno Registrado!'; icon = 'success'; }
            else if (mensaje === 'deleted') { title = 'Alumno Suspendido'; icon = 'info'; }
            else if (mensaje === 'edited') { title = '¡Cambios Guardados!'; icon = 'success'; }
            else if (mensaje === 'enabled') { title = '¡Alumno Habilitado!'; icon = 'success'; }
            
            if (title) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: title,
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                    background: '#111827',
                    color: '#e2e8f0'
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
    });
</script>

</body>
</html>