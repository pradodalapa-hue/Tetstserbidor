<?php
// HELENA CORE - LIVRO RAZÃO CENTRAL JDP - ULTRA ROBUSTO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers de CORS para liberar conexões externas de qualquer dispositivo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$file = __DIR__ . '/database_central.json';

// FORÇA A CRIAÇÃO DO ARQUIVO SE NÃO EXISTIR
if (!file_exists($file)) {
    $initialData = json_encode(["mensagens" => []], JSON_PRETTY_PRINT);
    
    // Tenta gravar. Se falhar, exibe o erro de permissão do servidor
    if (@file_put_contents($file, $initialData) === false) {
        $err = error_get_last();
        echo json_encode([
            "status" => "error", 
            "message" => "FALHA DE PERMISSAO: O PHP nao tem permissao para criar arquivos nesta pasta do seu servidor.",
            "detalhes" => $err['message'],
            "solucao" => "Altere as permissoes da pasta onde esta o ledger.php para 755 ou 777 no gerenciador de arquivos da sua hospedagem."
        ]);
        exit;
    }
    @chmod($file, 0777); // Garante acesso total de leitura/escrita ao arquivo criado
}

// 1. GRAVAÇÃO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (isset($data['payload'])) {
        $ledger = json_decode(file_get_contents($file), true);
        if (!$ledger) {
            $ledger = ["mensagens" => []];
        }
        
        $ledger['mensagens'][] = $data['payload'];
        
        if (file_put_contents($file, json_encode($ledger, JSON_PRETTY_PRINT)) === false) {
            echo json_encode(["status" => "error", "message" => "Nao foi possivel salvar dados no database_central.json. Verifique as permissoes do arquivo."]);
        } else {
            echo json_encode(["status" => "success", "message" => "Sinal gravado com sucesso no Livro Razao."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Payload vazio ou invalido."]);
    }
    exit;
}

// 2. LEITURA (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $content = file_get_contents($file);
    if ($content === false) {
        echo json_encode(["status" => "error", "message" => "Erro ao ler o arquivo database_central.json."]);
    } else {
        echo $content;
    }
    exit;
}
