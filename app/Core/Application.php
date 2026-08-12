<?php
namespace App\Core;

class Application {
    public static Application $app;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;

    public function __construct() {
        self::$app = $this;
        $GLOBALS['app_config'] = require __DIR__ . '/../../config/app.php';

        date_default_timezone_set($GLOBALS['app_config']['timezone'] ?? 'Asia/Kolkata');

        $dbConfig = require __DIR__ . '/../../config/database.php';
        Database::init(['connections' => ['mysql' => $dbConfig]]);

        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->router = new Router($this->request, $this->response);
    }

    public function run(): void {
        echo $this->router->resolve();
    }
}
