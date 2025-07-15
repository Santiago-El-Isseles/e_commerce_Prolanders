<?php
require_once 'connect.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $correo = $data['correo'] ?? '';
    $contrasena = $data['contrasena'] ?? '';

    if (empty($correo) || empty($contrasena)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, introduce correo y contraseña.']);
        exit;
    }

    $sql = "SELECT id, nombre, apellido, contrasena, rol FROM usuarios WHERE correo = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $correo);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nombre, $apellido, $hashed_password, $rol);
                $stmt->fetch();

                if (password_verify($contrasena, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['apellido'] = $apellido;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['rol'] = $rol;

                    echo json_encode([
                        'success' => true,
                        'message' => 'Inicio de sesión exitoso.',
                        'user' => [
                            'id' => $id,
                            'nombre' => $nombre,
                            'apellido' => $apellido,
                            'correo' => $correo,
                            'rol' => $rol
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró una cuenta con ese correo.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta.']);
    }

    $mysqli->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>