<?php 

class Router
{
    private $routes = [];
    private $basePath = '';

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function addPath($method, $path, $controller)
    {
        $this->routes[$method][$this->basePath . $path] = $controller;
    }

    public function addRoute($path, $controller, $method = 'GET')
    {
        $this->routes[$method][$this->basePath . $path] = $controller;
    }

    public function dispatch($path, $method)
    {
        foreach ($this->routes[$method] as $routePath => $controller) {
            if ($this->isMatchingRoute($routePath, $path)) {
                $params = $this->extractParams($routePath, $path);
                $response = $this->callController($controller, $params);
                return $response;
            }
        }
        return '404 - Not Found '.$path.' '.$method;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
    
    private function isMatchingRoute($routePath, $requestPath)
    {
        $routePath = rtrim($routePath, '/');
        $requestPath = rtrim($requestPath, '/');
        
        $pattern = str_replace('/', '\/', $routePath);
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';

        return preg_match($pattern, $requestPath);
    }

    private function extractParams($routePath, $requestPath)
    {
        $routeParts = explode('/', rtrim($routePath, '/'));
        $requestParts = explode('/', rtrim($requestPath, '/'));

        $params = [];

        foreach ($routeParts as $index => $part) {
            if (preg_match('/^\{[a-zA-Z0-9_]+\}$/', $part)) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $requestParts[$index];
            }
        }

        return $params;
    }

    private function callController($controller, $params)
    {
        // Obtener el nombre de la clase y el método del controlador
        list($className, $method) = explode('@', $controller);

        // Crear una instancia del controlador
        $controllerInstance = new $className();

        // Llamar al método del controlador y obtener la respuesta
        $response = $controllerInstance->$method($params);

        return $response;
    }
}