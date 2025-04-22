<?php
class PdfToText {

    private $pdf;
    private $text = '';

    public function __construct($file) {
        if (!file_exists($file)) {
            throw new Exception("El archivo no existe: $file");
        }
        $this->pdf = file_get_contents($file);
        $this->parsePDF();
    }

    private function parsePDF() {
        // Buscar los flujos de texto (stream...endstream)
        preg_match_all('/stream(.*?)endstream/s', $this->pdf, $matches);
        foreach ($matches[1] as $stream) {
            $stream = trim($stream);
            $this->text .= $this->extractTextFromStream($stream);
        }
    }

    private function extractTextFromStream($stream) {
        // Eliminar encabezados y pies innecesarios
        $text = '';
        // Buscar texto entre paréntesis (formatos básicos)
        if (preg_match_all('/\((.*?)\)/s', $stream, $matches)) {
            foreach ($matches[1] as $match) {
                $text .= $this->decodeText($match);
            }
        }
        return $text;
    }

    private function decodeText($text) {
        // Decodificar caracteres escapados en el texto
        $text = preg_replace_callback('/\\\\([nrtbf()\\\\])/', function($m) {
            $map = ['n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\b", 'f' => "\f", '(' => '(', ')' => ')', '\\' => '\\'];
            return $map[$m[1]] ?? $m[0];
        }, $text);
        return $text;
    }

    public function getText() {
        return $this->text;
    }
}
?>
