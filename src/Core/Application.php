<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CatalogController;
use App\Controllers\RentalController;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\RentalService;
use App\Storage\JsonStorage;

/**
 * Front controller: merakit dependency dan mencocokkan route "METHOD route".
 */
class Application
{
    /** @var array<string,array{0:object,1:string}> */
    private array $routes = [];

    public function __construct(string $rootPath)
    {
        $data = $rootPath . '/data';
        $mediaRepo = new MediaRepository(new JsonStorage($data . '/media.json'));
        $userRepo = new UserRepository(new JsonStorage($data . '/users.json'));
        $trxRepo = new TransactionRepository(new JsonStorage($data . '/transactions.json'));

        $view = new View($rootPath . '/views');
        $auth = new AuthService($userRepo);
        $rentals = new RentalService($mediaRepo, $trxRepo);

        $catalog = new CatalogController($view, $auth, $mediaRepo);
        $authCtl = new AuthController($view, $auth);
        $rental = new RentalController($view, $auth, $rentals, $trxRepo, $mediaRepo);
        $admin = new AdminController($view, $auth, $mediaRepo, $trxRepo, $userRepo);

        $this->routes = [
            'GET catalog' => [$catalog, 'index'],
            'GET login' => [$authCtl, 'showLogin'],
            'POST login' => [$authCtl, 'login'],
            'GET register' => [$authCtl, 'showRegister'],
            'POST register' => [$authCtl, 'register'],
            'POST logout' => [$authCtl, 'logout'],
            'GET rentals' => [$rental, 'myRentals'],
            'POST rent' => [$rental, 'rent'],
            'POST return' => [$rental, 'giveBack'],
            'GET admin' => [$admin, 'dashboard'],
            'POST admin-add' => [$admin, 'addMedia'],
            'POST admin-delete' => [$admin, 'deleteMedia'],
        ];
        $this->view = $view;
    }

    private View $view;

    public function run(): void
    {
        Session::start();

        $route = (string) ($_GET['r'] ?? 'catalog');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $key = $method . ' ' . $route;

        if (!isset($this->routes[$key])) {
            http_response_code(404);
            $this->view->render('errors/404', [], null);
            return;
        }

        if ($method === 'POST' && !Session::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Token CSRF tidak valid. Muat ulang halaman lalu coba lagi.';
            return;
        }

        [$controller, $action] = $this->routes[$key];
        $controller->$action();
    }
}
