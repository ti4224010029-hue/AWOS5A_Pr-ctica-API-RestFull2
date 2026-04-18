<?php
// ============================================
// eliminar_alumno.php - Solo deshabilita alumno (status = 0)
// ============================================
require_once 'conexion.php';

if (isset($_GET['matricula'])) {
    try {
        $pdo = getConnection();
        $matricula = $_GET['matricula'];

        // Solo actualiza el estatus a 0 (Inactivo / Suspendido)
        $sql = "UPDATE alumnos SET status = 0 WHERE matricula = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$matricula])) {
            // Redirige al listado principal con mensaje de éxito
            header("Location: cliente.php?mensaje=deleted");
            exit();
        } else {
            header("Location: cliente.php?mensaje=error");
            exit();
        }
    } catch (PDOException $e) {
        // Si hay error, redirige con mensaje de error
        header("Location: cliente.php?mensaje=error");
        exit();
    }
} else {
    header("Location: cliente.php");
    exit();
}
?>