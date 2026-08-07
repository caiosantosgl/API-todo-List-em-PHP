<?php 
    namespace Models;

    class Task {
        private $titulo;
        private $status;
        private $userId;
        private $criadoEm;

        public function __construct($titulo, $userId){
            if (empty(trim($titulo))){
                throw new \Exception("O título da tarefa não pode estar vazio.");
            }

            $this->titulo = $titulo;
            $this->userId = $userId;
            $this->status = 'nao_concluido';
            $this->criadoEm = time();
        }

        public function toArray(){
            return [
                'titulo' => $this->titulo,
                'status' => $this->status,
                'user_id' => $this->userId,
                'criado_em' => $this->criadoEm
            ];
        }
    }
?>