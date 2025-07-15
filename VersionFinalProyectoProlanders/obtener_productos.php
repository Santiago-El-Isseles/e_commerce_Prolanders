<?php
require_once 'connect.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id, nombre_producto, precio_producto FROM productos";
    $stmt = $mysqli->prepare($sql);

    $stmt->execute();
    
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    echo json_encode(['success' => true, 'productos' => $productos]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener productos: ' . $e->getMessage()]);
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?>