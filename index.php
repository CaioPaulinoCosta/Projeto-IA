<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

$uploadDir = 'uploads/';

function processImage($filePath) {
    $command = escapeshellcmd("python process_image.py " . escapeshellarg($filePath));
    $output = shell_exec($command);

    if (!$output) {
        return "Erro ao processar a imagem.";
    }

    $html = "<h1>Relatório de Análise de Imagem</h1>";
    $html .= "<p>" . nl2br(htmlspecialchars($output)) . "</p>";

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html); 

    $dompdf->setPaper('A4', 'portrait');

    $dompdf->render();

    $pdfOutputPath = 'relatorio.pdf';
    file_put_contents($pdfOutputPath, $dompdf->output()); 

    return $pdfOutputPath; 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $filePath = $uploadDir . basename($_FILES['image']['name']);
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
        $pdfPath = processImage($filePath);
        
        echo "Relatório gerado com sucesso: <a href='$pdfPath'>Baixar PDF</a>";
    } else {
        echo "Erro no upload da imagem.";
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data">
              Selecione uma imagem: <input type="file" name="image" />
              <input type="submit" value="Upload e Analisar" />
          </form>';
}
?>
