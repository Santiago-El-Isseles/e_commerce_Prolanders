<?php
require_once 'connect.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para agregar productos al carrito.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"), true);

    $producto_id = $data['producto_id'] ?? null;
    $cantidad = $data['cantidad'] ?? 1;

    if (empty($producto_id) || !is_numeric($producto_id) || $cantidad < 1) {
        echo json_encode(['success' => false, 'message' => 'Datos de producto inválidos.']);
        exit;
    }

    try {
        $sql_check = "SELECT id, cantidad_producto FROM carrito WHERE id_usuario = ? AND id_producto = ?";
        if ($stmt_check = $mysqli->prepare($sql_check)) {
            $stmt_check->bind_param("ii", $user_id, $producto_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $stmt_check->bind_result($carrito_item_id, $cantidad_actual);
                $stmt_check->fetch();
                $stmt_check->close();

                $nueva_cantidad = $cantidad_actual + $cantidad;
                $sql_update = "UPDATE carrito SET cantidad_producto = ? WHERE id = ?";
                if ($stmt_update = $mysqli->prepare($sql_update)) {
                    $stmt_update->bind_param("ii", $nueva_cantidad, $carrito_item_id);
                    if ($stmt_update->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Cantidad del producto actualizada en el carrito.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al actualizar la cantidad en el carrito: ' . $stmt_update->error]);
                    }
                    $stmt_update->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta de actualización.']);
                }
            } else {
                $stmt_check->close();

                $sql_insert = "INSERT INTO carrito (id_usuario, id_producto, cantidad_producto) VALUES (?, ?, ?)";
                if ($stmt_insert = $mysqli->prepare($sql_insert)) {
                    $stmt_insert->bind_param("iii", $user_id, $producto_id, $cantidad);
                    if ($stmt_insert->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al agregar el producto al carrito: ' . $stmt_insert->error]);
                    }
                    $stmt_insert->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta de inserción.']);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta de verificación del carrito.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
    } finally {
        if (isset($mysqli)) {
            $mysqli->close();
        }
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>