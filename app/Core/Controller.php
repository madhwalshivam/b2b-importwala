<?php
namespace App\Core;

abstract class Controller {
    protected Request $request;
    protected Response $response;
    protected Session $session;
    protected View $view;

    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->view = new View();
    }

    protected function render(string $view, array $params = []): string {
        return $this->view->render($view, $params);
    }

    protected function json(array $data, int $statusCode = 200): void {
        $this->response->json($data, $statusCode);
    }

    protected function redirect(string $url): void {
        $this->response->redirect($url);
    }

    protected function setFlash(string $key, string $message): void {
        $this->session->setFlash($key, $message);
    }

    protected function getFlash(string $key): ?string {
        return $this->session->getFlash($key);
    }
}
