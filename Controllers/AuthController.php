<?php 
    namespace Controllers;

    use Config\Database;

    class AuthController {
        private $auth;

        public function __construct(){
            $this->auth = Database::pegarInstancia()->getAuth();
        }

        public function cadastrar($nome, $email, $senha){
            try {
                if (strlen($senha) < 6){
                    http_response_code(400);

                    return [
                        "sucesso" => false,
                        "mensagem" => "A senha deve conter no mínimo 6 caracteres."
                    ];
                }

                $userPropriedades = [
                    "email" => $email,
                    "emailVerified" => false,
                    "password" => $senha,
                    "displayname" => $nome,
                    "disabled" => false
                ];

                $createdUser = $this->auth->createUser($userPropriedades);

                http_response_code(201);

                return [
                    "sucesso" => true,
                    "mensagem" => "Usuário cadastrado com sucesso.",
                    "uid" => $createdUser->uid
                ];
            
            } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e){
                http_response_code(409);

                return [
                    "sucesso" => false,
                    "mensagem" => "Este e-mail já está sendo usado por outra conta."
                ];
            } catch (\Exception $e){
                http_response_code(500);

                return [
                    "sucesso" => false,
                    "mensagem" => "Erro ao cadastrar: " . $e->getMessage()
                ];
            }
        }

        public function login($email, $senha){
            try {
                $loginResultado = $this->auth->signInWithEmailAndPassword($email, $senha);

                $uid = $loginResultado->firebaseUserId();
                $idToken = $loginResultado->idToken();

                if (session_status() === PHP_SESSION_NONE){
                    session_start();
                }

                $_SESSION["user_uid"] = $uid;
                $_SESSION["id_token"] = $idToken;
                $_SESSION["user_email"] = $email;

                http_response_code(200);

                return [
                    "sucesso" => true,
                    "mensagem" => "Login realizado com sucesso!",
                    "uid" => $uid
                ];
            } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e){
                http_response_code(401);

                return [
                    "sucesso" => false,
                    "mensagem" => "Senha incorreta."
                ];
            } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e){
                http_response_code(404);

                return [
                    "sucesso" => false,
                    "mensagem" => "Usuário não encontrado"
                ];
            } catch (\Exception $e){
                http_response_code(500);

                return [
                    "sucesso" => false,
                    "mensagem" => "Erro ao fazer login: " . $e->getMessage()
                ];
            }
        }

        public function logout(){
            if (session_status() === PHP_SESSION_NONE){
                session_start();
            }
            session_destroy();

            http_response_code(200);

            return [
                "sucesso" => true,
                "mensagem" => "Sessão encerrada com sucesso!"
            ];
        }

        public function testarConexao(){
            if ($this->auth){
                http_response_code(200);
                return ["sucesso" => true, "mensagem" => "Conexão feita com sucesso"];
            }
            http_response_code(500);
            return ["sucesso" => false, "mensagem" => "Falha ao conectar"];
        }
    }
?>