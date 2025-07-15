<?php
require_once 'connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión iniciada.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT nombre, apellido, correo, rol FROM usuarios WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            echo json_encode(['success' => true, 'usuario' => $usuario]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $mysqli->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error inesperado al obtener datos del perfil: ' . $e->getMessage()]);
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?>