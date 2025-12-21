<?php

declare(strict_types=1);

// Chargement de l'autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

date_default_timezone_set('Europe/Paris');

// Chargement des variables d'environnement
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Initialisation de l'environnement si nécessaire
if (!isset($_ENV['APP_ENV'])) {
    $_ENV['APP_ENV'] = 'development';
}

use App\Infrastructure\Container\ServiceContainer;

// Création du conteneur de services
$container = new ServiceContainer();

// Récupération de l'URI et de la méthode HTTP
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Chargement des routes
$routes = require __DIR__ . '/../config/routes.php';

// Fonction pour résoudre un contrôleur depuis le conteneur
function resolveController(string $controllerClass, ServiceContainer $container): object
{
    if (!str_starts_with($controllerClass, 'App\\')) {
        $controllerClass = 'App\\Interface\\Controller\\' . $controllerClass;
    }

    if (!$container->has($controllerClass)) {
        throw new \RuntimeException("Controller not found in container: $controllerClass");
    }

    return $container->get($controllerClass);
}

// Fonction pour appeler une méthode de contrôleur
function callControllerMethod(object $controller, string $methodName, array $data = []): mixed
{
    if (!method_exists($controller, $methodName)) {
        throw new \RuntimeException("Method $methodName not found in " . get_class($controller));
    }

    $reflection = new ReflectionMethod($controller, $methodName);
    $params = $reflection->getParameters();

    if (empty($params)) {
        return $controller->$methodName();
    } elseif (count($params) === 1) {
        $paramType = $params[0]->getType();
        if ($paramType && $paramType->getName() === 'array') {
            return $controller->$methodName($data);
        } else {
            return $controller->$methodName();
        }
    } else {
        return $controller->$methodName();
    }
}

// Fonction pour matcher une route avec des paramètres
function matchRoute(string $routePath, string $uri): ?array
{
    // Convertir le pattern de route en regex
    $pattern = preg_replace('/:(\w+)/', '(?P<$1>[^/]+)', $routePath);
    $pattern = '#^' . $pattern . '$#';

    if (preg_match($pattern, $uri, $matches)) {
        // Retourner les paramètres capturés
        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_numeric($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    return null;
}

// Recherche de la route correspondante
$matched = false;
foreach ($routes as $route) {
    [$method, $path, $handler] = $route;

    // Vérifier la méthode HTTP
    if ($method !== $requestMethod) {
        continue;
    }

    // Vérifier si la route correspond (avec ou sans paramètres)
    $params = null;
    if ($path === $uri) {
        $params = [];
    } else {
        $params = matchRoute($path, $uri);
    }

    if ($params !== null) {
        $matched = true;

        try {
            // Préparation des données
            $data = array_merge($_GET, $_POST, $params);

            // Gestion du handler
            $result = null;
            if (is_string($handler) && str_contains($handler, '::')) {
                // Format "Controller::method"
                [$controllerClass, $methodName] = explode('::', $handler);
                $controller = resolveController($controllerClass, $container);
                $result = callControllerMethod($controller, $methodName, $data);
            } elseif (is_array($handler) && count($handler) >= 2) {
                // Format [Class, 'method']
                [$controllerClass, $methodName] = $handler;
                $controller = resolveController($controllerClass, $container);
                $result = callControllerMethod($controller, $methodName, $data);
            } elseif (is_callable($handler)) {
                // Handler callable
                $result = $handler($data);
            }

            // Output handling
            if (is_array($result) || is_object($result)) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
            } elseif (is_string($result)) {
                echo $result;
            }

            exit;
        } catch (\Throwable $e) {
            error_log("Route error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

// Route non trouvée
if (!$matched) {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'Route non trouvée'], JSON_UNESCAPED_UNICODE);
}
