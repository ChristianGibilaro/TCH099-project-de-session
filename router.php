<?php
// Autoriser les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    http_response_code(200);
    exit();
}

function get($route, $path_to_include)
{
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                route($route, $path_to_include);
        }
}
function post($route, $path_to_include)
{
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                route($route, $path_to_include);
        }
}
function put($route, $path_to_include)
{
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
                route($route, $path_to_include);
        }
}
function patch($route, $path_to_include)
{
        if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
                route($route, $path_to_include);
        }
}
function delete($route, $path_to_include)
{
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
                route($route, $path_to_include);
        }
}
function any($route, $path_to_include)
{
        route($route, $path_to_include);
}

/**
 * Redirects to a specific page if the request matches the given parameters.
 *
 * @param array $conditions Key-value pairs to check in the request.
 * @param string $redirect_page The page to redirect to if conditions are met.
 */
function redirect_if_match(array $conditions, string $redirect_page)
{
    $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $request_url = rtrim($request_url, '/');
    $request_url = strtok($request_url, '?');
    $request_url_parts = explode('/', $request_url);

    foreach ($conditions as $key => $value) {
        // Check if the key exists in the request URL and matches the value
        if (!in_array($value, $request_url_parts)) {
            return; // If any condition is not met, exit the function
        }
    }

    // If all conditions are met, redirect to the specified page
    include_once __DIR__ . "/$redirect_page";
    exit();
}

function route($route, $path_to_include)
{
        $callback = $path_to_include;
        if (!is_callable($callback)) {
                if (!strpos($path_to_include, '.php')) {
                        $path_to_include .= '.php';
                }
        }

        
        
        if ($route == "/404") {
                include_once __DIR__ . "/$path_to_include";
                exit();
        }
        $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
        $request_url = rtrim($request_url, '/');
        $request_url = strtok($request_url, '?');
        $route_parts = explode('/', $route);
        $request_url_parts = explode('/', $request_url);
        array_shift($route_parts);
        array_shift($request_url_parts);
        if(count($request_url_parts)>0 && $request_url_parts[count($request_url_parts)-1]==""){
                array_pop($request_url_parts);
        }
        if ($route_parts[0] == '' && count($request_url_parts) == 0) {
                // Callback function
                if (is_callable($callback)) {
                        call_user_func_array($callback, []);
                        exit();
                }
                include_once __DIR__ . "/$path_to_include";
                exit();
        }

        if (count($route_parts) != count($request_url_parts)) {
                return;
        }
        $parameters = [];
        for ($__i__ = 0; $__i__ < count($route_parts); $__i__++) {
                $route_part = $route_parts[$__i__];
                if (preg_match("/^[$]/", $route_part)) {
                        $route_part = ltrim($route_part, '$');
                        array_push($parameters, $request_url_parts[$__i__]);
                        $$route_part = $request_url_parts[$__i__];
                } else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
                        return;
                }
        }

        if (is_callable($callback)) {
                // Si c'est une fonction anonyme (Closure), l'exécuter directement
                call_user_func_array($callback, $parameters);
                exit();
        } else {
                // Sinon, inclure le fichier PHP correspondant
                if (!strpos($path_to_include, '.php')) {
                        $path_to_include .= '.php';
                }
                include_once __DIR__ . "/$path_to_include";
                exit();
        }
}
function out($text)
{
        echo htmlspecialchars($text);
}

function set_csrf()
{
        // NOTE: session_start() was called in session_demarrage.php included in config.php
        // which might be included before this runs depending on execution order.
        // Calling it again might cause issues if sessions aren't configured properly.
        // Consider ensuring session_start() is called only once.
        if (session_status() === PHP_SESSION_NONE) {
       session_start();
    }
        if (!isset($_SESSION["csrf"])) {
                $_SESSION["csrf"] = bin2hex(random_bytes(50));
        }
        echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
}

function is_csrf_valid()
{
    // See note in set_csrf() about session_start()
    if (session_status() === PHP_SESSION_NONE) {
       session_start();
    }
        if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) {
                return false;
        }
        if ($_SESSION['csrf'] != $_POST['csrf']) {
                return false;
        }
        return true;
}
?>