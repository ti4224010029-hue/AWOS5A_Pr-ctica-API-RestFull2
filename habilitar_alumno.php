<?php
// ============================================
// habilitar_alumno.php - Solo habilita alumno (status = 1)
// ============================================
require_once 'conexion.php';

if (isset($_GET['matricula'])) {
    try {
        $pdo = getConnection();
        $matricula = $_GET['matricula'];

        // Solo actualiza el estatus a 1 (Activo)
        $sql = "UPDATE alumnos SET status = 1 WHERE matricula = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$matricula])) {
            // Redirige al listado principal con mensaje de éxito
            header("Location: cliente.php?mensaje=enabled");
            exit();
        } else {
            header("Location: cliente.php?mensaje=error");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: cliente.php?mensaje=error");
        exit();
    }
} else {
    header("Location: cliente.php");
    exit();
}
?>