<?php 
    require_once __DIR__ . '/Config/Database.php';
    require_once __DIR__ . '/Models/Task.php';
    require_once __DIR__ . '/Controllers/AuthController.php';
    require_once __DIR__ . '/Controllers/TaskController.php';

    use Controllers\AuthController;
    use Controllers\TaskController;

    // Para depurar localmente, troque para '1' temporariamente.
    ini_set('display_errors', '0');
    error_reporting(E_ALL);

    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Rede de segurança: qualquer erro fatal não tratado (ex.: falha ao conectar no Firebase)
    // vira uma resposta JSON com 500, em vez de uma página HTML de erro quebrando o front-end.
    set_exception_handler(function (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro interno no servidor: ' . $e->getMessage()
        ]);
        exit;
    });

    $metodo = $_SERVER['REQUEST_METHOD'] ?? ''; // peguei o método

    $baseDir = dirname($_SERVER['SCRIPT_NAME']); // Deixa só a pasta, removendo o "index.php" do final. Remove o nome do arquivo no final, deixando só o caminho da pasta.
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // URL completa, "PHP_URL_PATH" pega só o caminho e ignora o resto. o "parse_url" pega completo, e o "PHP_URL_PATH" pega só o caminho.

    if ($baseDir !== '/' && $baseDir !== '\\') { // Se não estiver na raiz, executa (na raiz, não há prefixo de pasta para remover).
        $uri = substr($uri, strlen($baseDir)); // Transforma a URI apenas no necessário, removendo o início da URL até chegar no caminho que interessa.
    }

    $uri = trim($uri, "/"); // Removendo as barras do início e fim
    $partes = explode('/', $uri); // Pegando as partes importantes da URL (URI). Por exemplo: vira: partes = ['usuarios1', id]. Vira um array, por exemplos: partes['usuarios1/5'] -> =['usuarios1', '5']
    $recurso = $partes[0] ?? ''; // Pega a parte da URL que interessa.

    $id = isset($partes[1]) && $partes[1] !== '' ? $partes[1] : null; // Pega o id, se não tiver, o valor é nulo.

    $dadosEntrada = json_decode(file_get_contents('php://input'), true) ?? [];

    // Rota de diagnóstico: útil para testar isoladamente se a conexão com o Firebase
    // está funcionando no servidor, sem depender de login/cadastro/tarefas.
    if ($recurso === 'testar-conexao' && $metodo === 'GET') {
        $controller = new AuthController();
        $resultado = $controller->testarConexao();
        echo json_encode($resultado);
        exit;
    }

    if ($recurso === 'login' && $metodo === 'POST') {
        $controller = new AuthController();
        $resultado = $controller->login($dadosEntrada['email'] ?? '', $dadosEntrada['senha'] ?? '');
        echo json_encode($resultado);
        exit;
    }

    if ($recurso === 'cadastrar' && $metodo === 'POST') {
        $controller = new AuthController();
        $resultado = $controller->cadastrar($dadosEntrada['nome'] ?? '', $dadosEntrada['email'] ?? '', $dadosEntrada['senha'] ?? '');
        echo json_encode($resultado);
        exit;
    }

    if ($recurso === 'logout' && $metodo === 'POST') {
        $controller = new AuthController();
        $resultado = $controller->logout();
        echo json_encode($resultado);
        exit;
    }

    if ($recurso === 'tarefas') {
        $controller = new TaskController();

        if ($metodo === 'GET') {
            $userId = $_GET['user_id'] ?? '';
            $resultado = $controller->listarTarefas($userId);

        } elseif ($metodo === 'POST') {
            $resultado = $controller->criarTarefa($dadosEntrada['titulo'] ?? '', $dadosEntrada['user_id'] ?? '');

        } elseif ($metodo === 'PUT' && $id) {
            $resultado = $controller->atualizarStatus($id, $dadosEntrada['status'] ?? '');

        } elseif ($metodo === 'DELETE' && $id) {
            $resultado = $controller->deletarTarefa($id);

        } else {
            http_response_code(404);
            $resultado = ['sucesso' => false, 'mensagem' => 'Método ou ID inválido para tarefas.'];
        }

        echo json_encode($resultado);
        exit;
    }

    http_response_code(404);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Recurso não encontrado.']);
    exit;
?>