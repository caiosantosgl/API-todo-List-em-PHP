<?php
    namespace Config;

    require_once __DIR__ . '/../vendor/autoload.php';

    use Kreait\Firebase\Factory;

    class Database {
        private static $instance = null;

        private $auth;
        private $firestore;

        private function __construct(){
            try {
                $firebaseCredentials = getenv('FIREBASE_CREDENTIALS');

                if ($firebaseCredentials) {

                    $credenciais = json_decode($firebaseCredentials, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception(
                            "As credenciais do Firebase são inválidas: " .
                            json_last_error_msg()
                        );
                    }

                    $factory = (new Factory)
                        ->withServiceAccount($credenciais);

                } else {

                    $caminhoCredenciais = __DIR__ . '/../firebase_credentials.json';

                    if (!file_exists($caminhoCredenciais)) {
                        throw new \Exception(
                            "FIREBASE_CREDENTIALS não configurada e " .
                            "firebase_credentials.json não encontrado."
                        );
                    }

                    putenv("GOOGLE_APPLICATION_CREDENTIALS={$caminhoCredenciais}");

                    $factory = (new Factory)
                        ->withServiceAccount($caminhoCredenciais);
                }

                $this->auth = $factory->createAuth();

                $this->firestore = $factory
                    ->createFirestore()
                    ->database();

            } catch (\Throwable $e) {

                http_response_code(500);

                header('Content-Type: application/json; charset=utf-8');

                die(json_encode([
                    "sucesso" => false,
                    "erro" => $e->getMessage(),
                    "tipo" => get_class($e)
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        public static function pegarInstancia(){
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function getAuth(){
            return $this->auth;
        }

        public function getFirestore(){
            return $this->firestore;
        }
    }
?>