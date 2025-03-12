<?php

// Define la URL de la API de Ollama
$ollamaUrl = 'http://localhost:11434/api/generate';

// Verifica si se recibió el 'prompt' en el cuerpo de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {
    $prompt = $_POST['prompt'];
    
    // Prepara los datos para enviar a la API de Ollama
    $data = [
        'model' => 'deepseek-r1:8b',
        'prompt' => $prompt,
        'stream' => false
    ];
    
    // Inicializa cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ollamaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    
    // Ejecuta la solicitud y obtiene la respuesta
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Si la solicitud fue exitosa
    if ($httpCode === 200) {
        // Decodifica la respuesta de Ollama
        $responseData = json_decode($response, true);

        if (isset($responseData['response'])) {
            // Elimina la etiqueta <think> del resultado
            $output = $responseData['response'];
            $output = preg_replace('/<think>.*?<\/think>/s', '', $output);
            
            // Muestra la respuesta procesada
            echo $output;
        } else {
            echo "Error: No se encontró la clave 'response' en la respuesta de Ollama.";
        }
    } else {
        echo "Error al contactar Ollama. Código de estado HTTP: $httpCode";
    }
} else {
    echo "Por favor, proporcione el campo 'prompt' en la solicitud POST.";
}

?>
