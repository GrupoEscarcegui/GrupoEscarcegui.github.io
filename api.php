<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ixtapa_db';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Conexión fallida"]);
    exit();
}

$action = $_GET['action'] ?? '';

// 1. Obtener únicamente los lotes que ya han sido comprados/reservados
if ($action == 'get_lotes') {
    $result = $conn->query("SELECT lote FROM tickets");
    $ocupados = [];
    while($row = $result->fetch_assoc()) {
        $ocupados[] = intval($row['lote']);
    }
    echo json_encode($ocupados);
}

// 2. Guardar el ticket y generar referencia en incremento
if ($action == 'save_ticket') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $nombre = $data['nombre'] ?? '';
    $correo = $data['correo'] ?? '';
    $telefono = $data['telefono'] ?? '';
    $lote = $data['lote'] ?? 0;
    $monto = $data['monto'] ?? 0;
    
    // Generar referencia en incremento basada en el total de tickets registrados
    $resultRef = $conn->query("SELECT COUNT(*) as total FROM tickets");
    $rowRef = $resultRef->fetch_assoc();
    $nextNum = 1001 + intval($rowRef['total']);
    $referencia = "IXT-" . $nextNum;

    // Guardar el ticket en la base de datos
    $sql = "INSERT INTO tickets (referencia, nombre_cliente, correo, telefono, lote, monto_enganche) 
            VALUES ('$referencia', '$nombre', '$correo', '$telefono', $lote, $monto)";
    
    if($conn->query($sql)) {
        echo json_encode(["success" => true, "referencia" => $referencia]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}
$conn->close();
?>