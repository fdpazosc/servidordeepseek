<?php

function extractTextFromPDF($filePath) {
    /*$outputFile = tempnam(sys_get_temp_dir(), 'pdftext'); // Archivo temporal para el texto extraído
    $command = "pdftotext '{$filePath}' '{$outputFile}'"; // Comando para convertir PDF a texto
    exec($command); // Ejecuta el comando

    // Lee el contenido del archivo de texto generado
    $text = file_get_contents($outputFile);

    // Elimina el archivo temporal
    unlink($outputFile);

    return $text;*/


    require_once 'PdfToText.php';

    $pdf = new PdfToText($filePath);
    //echo "Contenido extraído de: $filePath\n"; // Mensaje de depuración

    return nl2br(htmlspecialchars($pdf->getText()));
}

// Función para obtener el contenido de todos los PDFs en una carpeta
function getContentFromPDFs($directory) {
    $content = '';
    $files = scandir($directory);

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $filePath = $directory . '/' . $file;
            $content .= extractTextFromPDF($filePath) . "\n\n";
            //echo "Contenido extraído de: $content\n"; // Mensaje de depuración
        }
    }

    return $content;
}

$pdfDirectory = 'documentos_pdf';

$pdfContent = getContentFromPDFs($pdfDirectory);

//echo $pdfContent . "\n\n";

// Define la URL de la API de Ollama
$ollamaUrl = 'http://localhost:11434/api/generate';

$retorno = array(
    'success' => false,
    'respuesta' => null,
    'error' => 'Error inesperado'
);

// Verifica si se recibió el 'prompt' en el cuerpo de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {
    $prompt = $_POST['prompt'];
    
    // Prepara los datos para enviar a la API de Ollama
    $data = [
        'model' => 'deepseek-r1:8b',
        'prompt' => "You are an assistant named RODI, created by the IT Department of the Pontifical Catholic University of Ecuador.
                     Your task is to respond accurately and in a friendly, conversational style and in the same language to the 
                     following prompt: $prompt",
        'stream' => false,
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
            $output = trim($output, '\n');
            $output = trim($output, '\t');
            $output = trim($output, '\r\n');
            $output = trim($output, '\r');
            $output = trim($output);
            
            // Muestra la respuesta procesada
            $retorno['success'] = true;
            $retorno['respuesta'] = $output;
            $retorno['error'] = null;
        } else {
            $retorno['error'] = 'Error: No se encontró la clave "response" en la respuesta de Ollama.';
        }
    } else {
        $retorno['error'] = 'Error al contactar Ollama. Código de estado HTTP: ' . $httpCode;
    }
} else {
    $retorno['error'] = 'Error: El campo "prompt" no fue proporcionado.';
}

// Devuelve el resultado en formato JSON
header('Content-Type: application/json');
echo json_encode($retorno);

?>
