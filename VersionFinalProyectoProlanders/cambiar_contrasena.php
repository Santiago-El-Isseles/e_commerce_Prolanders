<?php
require_once 'connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión iniciada.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"), true);
    $nueva_contrasena = $data['nueva_contrasena'] ?? '';

    if (empty($nueva_contrasena) || strlen($nueva_contrasena) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña es demasiado corta o está vacía.']);
        exit;
    }

    $hashed_password = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

    try {
        $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la contraseña (quizás ya es la misma).']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al ejecutar la actualización: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $mysqli->error]);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error inesperado al cambiar contraseña: ' . $e->getMessage()]);
    } finally {
        if (isset($mysqli)) {
            $mysqli->close();
        }
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>