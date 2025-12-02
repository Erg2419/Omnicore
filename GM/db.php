<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "GM";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8mb4");

// Función para obtener total de calorías del día
function obtenerTotalCalorias($conn, $usuario_id) {
    $sql = "SELECT SUM(calorias) AS total_calorias 
            FROM registro_comidas 
            WHERE usuario_id = ? AND DATE(fecha_registro) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['total_calorias'] ?? 0;
}

// Función para obtener calorías quemadas del día
function obtenerCaloriasQuemadas($conn, $usuario_id) {
    $sql = "SELECT SUM(calorias_quemadas) AS total_quemadas 
            FROM ejercicios 
            WHERE usuario_id = ? AND DATE(fecha_registro) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['total_quemadas'] ?? 0;
}

// API Key de Gemini - TU KEY REAL
$GROQ_API_KEY = "AIzaSyB1fOa_AuH3OjipLBSiY75N_h11MA55dF8";

// Función para analizar imagen con Gemini
function analizarImagenComida($imagen_path) {
    global $GEMINI_API_KEY;
    
    if (!file_exists($imagen_path)) {
        return ["error" => "La imagen no existe en: " . $imagen_path];
    }
    
    // Verificar que la API key esté configurada
    if (empty($GEMINI_API_KEY)) {
        return ["error" => "API Key no configurada"];
    }
    
    // Codificar imagen en base64
    $imagen_data = base64_encode(file_get_contents($imagen_path));
    
    // Determinar el tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $imagen_path);
    finfo_close($finfo);
    
    // Preparar datos para la API de Gemini
    $data = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => "Analiza esta imagen de comida y devuelve SOLO un JSON con esta estructura exacta:\n\n{\n  \"nombre\": \"nombre del alimento\",\n  \"calorias\": numero,\n  \"proteina\": numero,\n  \"grasa\": numero,\n  \"carbohidrato\": numero,\n  \"descripcion\": \"breve descripcion\"\n}\n\nSi no puedes identificar algún valor, usa 0. Responde ÚNICAMENTE con el JSON, sin texto adicional."
                    ],
                    [
                        "inline_data" => [
                            "mime_type" => $mime_type,
                            "data" => $imagen_data
                        ]
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.1,
            "topK" => 32,
            "topP" => 1,
            "maxOutputTokens" => 2048,
        ]
    ];
    
    // Configurar cURL para Gemini
    $ch = curl_init();
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro-vision:generateContent?key=" . $GEMINI_API_KEY;
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Log para debugging
    error_log("Gemini API - Code: $http_code, Error: $curl_error");
    
    if ($http_code != 200) {
        $error_info = "Error HTTP: $http_code";
        if ($response) {
            $error_response = json_decode($response, true);
            if (isset($error_response['error']['message'])) {
                $error_info .= " - " . $error_response['error']['message'];
            } else {
                $error_info .= " - " . substr($response, 0, 200);
            }
        }
        return ["error" => $error_info];
    }
    
    if (empty($response)) {
        return ["error" => "Respuesta vacía de la API"];
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ["error" => "Error decodificando JSON de respuesta: " . json_last_error_msg()];
    }
    
    // Verificar estructura de respuesta
    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return ["error" => "Estructura de respuesta inválida de la API"];
    }
    
    $texto_respuesta = $result['candidates'][0]['content']['parts'][0]['text'];
    
    if (empty($texto_respuesta)) {
        return ["error" => "La API no devolvió texto en la respuesta"];
    }
    
    // Limpiar y extraer JSON
    $texto_limpio = trim($texto_respuesta);
    
    // Buscar JSON en la respuesta
    $json_inicio = strpos($texto_limpio, '{');
    $json_fin = strrpos($texto_limpio, '}');
    
    if ($json_inicio === false || $json_fin === false) {
        return ["error" => "No se encontró JSON en la respuesta de la IA"];
    }
    
    $json_str = substr($texto_limpio, $json_inicio, $json_fin - $json_inicio + 1);
    $datos_comida = json_decode($json_str, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ["error" => "Error decodificando JSON de la IA: " . json_last_error_msg() . " - Texto: " . substr($json_str, 0, 100)];
    }
    
    // Validar estructura básica
    if (!isset($datos_comida['nombre'])) {
        return ["error" => "El JSON no tiene la estructura esperada"];
    }
    
    // Asegurar que todos los campos numéricos tengan valores
    $datos_comida['calorias'] = floatval($datos_comida['calorias'] ?? 0);
    $datos_comida['proteina'] = floatval($datos_comida['proteina'] ?? 0);
    $datos_comida['grasa'] = floatval($datos_comida['grasa'] ?? 0);
    $datos_comida['carbohidrato'] = floatval($datos_comida['carbohidrato'] ?? 0);
    $datos_comida['descripcion'] = $datos_comida['descripcion'] ?? "Analizado por Gemini AI";
    
    return $datos_comida;
}

// Función de respaldo en caso de error (modo demo)
function analizarImagenDemo($imagen_path) {
    // Base de datos de alimentos demo
    $alimentos_demo = [
        [
            "nombre" => "Ensalada César con Pollo",
            "calorias" => 320,
            "proteina" => 25,
            "grasa" => 12,
            "carbohidrato" => 18,
            "descripcion" => "Ensalada fresca con pollo a la parrilla, lechuga romana y aderezo césar"
        ],
        [
            "nombre" => "Sándwich de Pavo Integral",
            "calorias" => 280,
            "proteina" => 18,
            "grasa" => 8,
            "carbohidrato" => 32,
            "descripcion" => "Sándwich en pan integral con pavo, lechuga, tomate y mostaza"
        ],
        [
            "nombre" => "Bowl de Frutas Tropicales",
            "calorias" => 150,
            "proteina" => 2,
            "grasa" => 1,
            "carbohidrato" => 35,
            "descripcion" => "Mezcla de frutas frescas: piña, mango, papaya y fresas"
        ]
    ];
    
    // Seleccionar alimento aleatorio pero consistente
    $nombre_archivo = basename($imagen_path);
    $hash = crc32($nombre_archivo);
    $indice = abs($hash) % count($alimentos_demo);
    
    $analisis = $alimentos_demo[$indice];
    $analisis['nota_demo'] = "Modo demo activado - API no disponible";
    
    return $analisis;
}

// Función principal mejorada con respaldo
function analizarImagenComidaMejorada($imagen_path) {
    // Primero intentar con la API real
    $resultado = analizarImagenComida($imagen_path);
    
    // Si hay error, usar modo demo
    if (isset($resultado['error'])) {
        error_log("Error en API Gemini, usando modo demo: " . $resultado['error']);
        return analizarImagenDemo($imagen_path);
    }
    
    return $resultado;
}

// Función para verificar metas
function verificarMetas($conn, $usuario_id) {
    $total_hoy = obtenerTotalCalorias($conn, $usuario_id);
    $meta = $conn->query("SELECT meta_calorias FROM metas WHERE usuario_id='$usuario_id' ORDER BY fecha_creacion DESC LIMIT 1");
    
    if($meta && $meta->num_rows > 0) {
        $meta_data = $meta->fetch_assoc();
        if($total_hoy >= $meta_data['meta_calorias']) {
            $conn->query("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) 
                         VALUES ('$usuario_id', 'Meta Alcanzada', '¡Felicidades! Has alcanzado tu meta diaria de calorías', 'meta')");
            
            $conn->query("INSERT INTO logros (usuario_id, tipo_logro, descripcion) 
                         VALUES ('$usuario_id', 'alimentacion', 'Meta diaria de calorías alcanzada')");
        }
    }
}
?>