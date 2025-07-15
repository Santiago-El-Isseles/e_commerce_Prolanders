<?php
require_once 'connect.php';

header('Content-Type: application/json');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';
    $correo = $data['correo'] ?? '';
    $contrasena = $data['contrasena'] ?? '';
    $confirmar_contrasena = $data['confirmar_contrasena'] ?? '';

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($contrasena) || empty($confirmar_contrasena)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Formato de correo electrónico inválido.']);
        exit;
    }

    if ($contrasena !== $confirmar_contrasena) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
        exit;
    }

    $sql_check_email = "SELECT id FROM usuarios WHERE correo = ?";
    if ($stmt_check_email = $mysqli->prepare($sql_check_email)) {
        $stmt_check_email->bind_param("s", $correo);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();
        if ($stmt_check_email->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado.']);
            $stmt_check_email->close();
            exit;
        }
        $stmt_check_email->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta de verificación de correo.']);
        exit;
    }

    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

    $sql_insert_user = "INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES (?, ?, ?, ?, 'normal')";

    if ($stmt_insert_user = $mysqli->prepare($sql_insert_user)) {
        $stmt_insert_user->bind_param("ssss", $nombre, $apellido, $correo, $hashed_password);

        if ($stmt_insert_user->execute()) {
            echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $stmt_insert_user->error]);
        }

        $stmt_insert_user->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta de registro.']);
    }

    $mysqli->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>