<?php

session_start();

use App\Controllers\ApartmentReviewController;
use App\Controllers\ApartmentsController;
use App\Controllers\HomeController;
use App\Controllers\ReservationsController;
use App\Controllers\UsersController;
use App\Redirect;
use App\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Carbon\Carbon;

require_once 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    //Home page
    $r->addRoute('GET', '/', [HomeController::class, 'home']);

    // Users
    $r->addRoute('GET', '/users', [UsersController::class, 'index']);
    $r->addRoute('GET', '/users/{id:\d+}', [UsersController::class, 'show']);

    // Signup
    $r->addRoute('GET', '/users/signup', [UsersController::class, 'signUp']);
    $r->addRoute('POST', '/users', [UsersController::class, 'register']);

    // Sign in & log out
    $r->addRoute('GET', '/users/login', [UsersController::class, 'signIn']);
    $r->addRoute('POST', '/users/login', [UsersController::class, 'enter']);
    $r->addRoute('POST', '/logout', [UsersController::class, 'logout']);

    //Error page
    $r->addRoute('GET', '/errors', [UsersController::class, 'error']);


    // Apartments
    $r->addRoute('GET', '/apartments', [ApartmentsController::class, 'index']);
    $r->addRoute('GET', '/apartments/{id:\d+}', [ApartmentsController::class, 'show']);

    $r->addRoute('GET', '/apartments/add', [ApartmentsController::class, 'add']);
    $r->addRoute('POST', '/apartments', [ApartmentsController::class, 'save']);

    $r->addRoute('POST', '/apartments/{id:\d+}/delete', [ApartmentsController::class, 'delete']);

    $r->addRoute('GET', '/apartments/{id:\d+}/edit', [ApartmentsController::class, 'edit']);
    $r->addRoute('POST', '/apartments/{id:\d+}/update', [ApartmentsController::class, 'update']);

    // Reviews
    $r->addRoute('POST', '/apartments/{apartmentId:\d+}/review', [ApartmentReviewController::class, 'storeReview']);
    $r->addRoute('GET', '/apartments/{apartmentId:\d+}/review', [ApartmentReviewController::class, 'createReview']);
    $r->addRoute('POST', '/apartments/{apartmentId:\d+}/review/{id:\d+}/deletereview', [ApartmentReviewController::class, 'deleteReview']);

    // Reservations

    $r->addRoute('GET', '/apartments/{id:\d+}/reservations', [ReservationsController::class, 'show']);
    $r->addRoute('POST', '/apartments/{id:\d+}/reserve', [ReservationsController::class, 'reserve']);
    $r->addRoute('GET', '/reservations/error', [ReservationsController::class, 'error']);

});


// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        var_dump('404 Not Found');
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        var_dump('405 Method Not Allowed');
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller =  $routeInfo[1][0];
        $method = $routeInfo[1][1];

        /** @var View $response */
        $response = (new $controller)->$method($routeInfo[2]);
        $twig = new Environment(new FilesystemLoader('app/Views'));

        if ($response instanceof View) {
            echo $twig->render($response->getPath() . '.html', $response->getData());
        }

        if ($response instanceof Redirect) {
            header('Location:'.$response->getLocation());
            exit;
        }
        break;
}