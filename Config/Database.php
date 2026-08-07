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

                // Decodifica as credenciais vindas do Render
                $credenciais = json_decode($firebaseCredentials, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception(
                        "As credenciais do Firebase são inválidas: " .
                        json_last_error_msg()
                    );
                }

                /**
                 * Cria um arquivo temporário com as credenciais.
                 * Isso permite que o Google Cloud Firestore utilize
                 * Application Default Credentials no ambiente do Render.
                 */
                $arquivoTemporario = tempnam(sys_get_temp_dir(), 'firebase_');

                if ($arquivoTemporario === false) {
                    throw new \Exception(
                        "Não foi possível criar o arquivo temporário das credenciais."
                    );
                }

                $resultado = file_put_contents(
                    $arquivoTemporario,
                    $firebaseCredentials
                );

                if ($resultado === false) {
                    throw new \Exception(
                        "Não foi possível gravar as credenciais no arquivo temporário."
                    );
                }

                putenv("GOOGLE_APPLICATION_CREDENTIALS={$arquivoTemporario}");

                $factory = (new Factory)
                    ->withServiceAccount($credenciais);

            } else {

                /**
                 * Ambiente local:
                 * utiliza o arquivo firebase_credentials.json
                 */
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

            // Firebase Authentication
            $this->auth = $factory->createAuth();

            // Firestore
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