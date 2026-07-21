php
<?php
// HELENA CORE - LIVRO RAZÃO CENTRAL JDP (SISTEMA DE ARQUIVO ÚNICO)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$file = __DIR__ . '/database_central.json';

// Inicializa o Livro Razão se ele não existir
if (!file_exists($file)) {
    file_put_contents($file, json_encode(["mensagens" => []], JSON_PRETTY_PRINT));
}

// 1. ESCRITA NO LIVRO RAZÃO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (isset($data['payload'])) {
        $ledger = json_decode(file_get_contents($file), true);
        
        // Insere o pacote criptografado no Livro Razão
        $ledger['mensagens'][] = $data['payload'];
        
        // Grava no silício do servidor
        file_put_contents($file, json_encode($ledger, JSON_PRETTY_PRINT));
        echo json_encode(["status" => "success", "message" => "Gravado no Livro Razao"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Payload invalido"]);
    }
    exit;
}

// 2. LEITURA DO LIVRO RAZÃO (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo file_get_contents($file);
    exit;
}
