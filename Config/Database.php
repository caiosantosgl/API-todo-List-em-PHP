<?php
    namespace Config;

    require_once __DIR__ . '/../vendor/autoload.php';

    use Kreait\Firebase\Factory;

    class Database {
        private static $instance = null;

        private $auth;
        private $firestore;

        private function __construct(){
            $firebaseCredentials = getenv('FIREBASE_CREDENTIALS');

            if ($firebaseCredentials) {

                $credenciais = json_decode($firebaseCredentials, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("As credenciais do Firebase são inválidas.");
                }

                $factory = (new Factory)
                    ->withServiceAccount($credenciais);

            } else {
                $caminhoCredenciais = __DIR__ . '/../firebase_credentials.json';

                if (!file_exists($caminhoCredenciais)) {
                    throw new \Exception(
                        "Arquivo firebase_credentials.json não encontrado."
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