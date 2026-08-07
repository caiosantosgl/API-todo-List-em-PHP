<?php 
    namespace Controllers;

    use Config\Database;
    use Models\Task;

    class TaskController {
        private $firestore;

        public function __construct(){
            $this->firestore = Database::pegarInstancia()->getFirestore();
        }

        public function criarTarefa($titulo, $userId){
            try {
                if (empty(trim($titulo))){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "O título da tarefa não pode estar vazio."
                    ];
                }

                if (empty($userId)){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "É necessário informar o usuário da tarefa."
                    ];
                }

                $tarefasExistentes = $this->firestore->collection('tarefas')
                    ->where('user_id', '=', $userId)
                    ->documents();

                if (iterator_count($tarefasExistentes) > 10){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "Limite atingido! Cada usuário só pode ter 10 tarefas!"
                    ];
                }

                $novaTarefa = new Task($titulo, $userId);

                $documento = $this->firestore->collection("tarefas")->add($novaTarefa->toArray());

                http_response_code(201);

                return [
                    "sucesso" => true,
                    "mensagem" => "Tarefa criada com sucesso!",
                    "tarefa_id" => $documento->id()
                ];
            } catch (\Exception $e){
                http_response_code(500);

                return [
                    "sucesso" => false,
                    "mensagem" => "Erro ao criar tarefa: " . $e->getMessage()
                ];
            }
        }

        public function listarTarefas($userId){
            try {
                if (empty($userId)){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "O ID do usuário é obrigatório."
                    ];
                }

                $query = $this->firestore->collection('tarefas')->where('user_id', '=', $userId);
                $documentos = $query->documents();
                $listaDeTarefas = [];

                foreach ($documentos as $doc){
                    if ($doc->exists()){
                        $dados = $doc->data();
                        $dados['id'] = $doc->id();
                        $listaDeTarefas[] = $dados;
                    }
                }

                http_response_code(200);

                return [
                    "sucesso" => true,
                    "total" => count($listaDeTarefas),
                    "tarefas" => $listaDeTarefas
                ];
            } catch (\Exception $e){
                http_response_code(500);

                return [
                    "sucesso" => false,
                    "mensagem" => "Erro ao listar tarefas: " . $e->getMessage()
                ];
            }
        }

        public function atualizarStatus($tarefaId, $novoStatus){
            try {
                if(empty($tarefaId)){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "O ID da tarefa é obrigatório."
                    ];
                }

                $statusPermetidos = ['nao_concluido', 'fazendo', 'concluido'];

                if (!in_array($novoStatus, $statusPermetidos)){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "Status inválido. Escolha apenas: nao_concluido, fazendo ou concluido."
                    ];
                }

                $this->firestore->collection('tarefas')->document($tarefaId)->update([
                    ['path' => 'status', 'value' => $novoStatus]
                ]);

                http_response_code(200);

                return [
                    "sucesso" => true,
                    "mensagem" => "Status da tarefa atualizado com sucesso!"
                ];
            } catch (\Exception $e){
                http_response_code(500);

                return [
                    "sucesso" => false,
                    "mensagem" => "Erro ao atualizar tarefa: " . $e->getMessage()
                ];
            }
        }

        public function deletarTarefa($tarefaId){
            try {
                if (empty($tarefaId)){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "O ID da tarefa é obrigatório para a exclusão."
                    ];
                }

                $this->firestore->collection('tarefas')->document($tarefaId)->delete();

                http_response_code(200);

                return [
                    "sucesso" => true,
                    "mensagem" => "Tarefa deletada com sucesso!"
                ];
            } catch (\Exception $e) {
                http_response_code(500);

                return [
                    "sucesso" => false,
                    "mensagem" => "Erro ao deletar tarefa: " . $e->getMessage()
                ];
            }
        }
    }
?>