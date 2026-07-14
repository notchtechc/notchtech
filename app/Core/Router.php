<?php
class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void  { $this->add('GET',  $path, $handler); }
    public function post(string $path, $handler): void { $this->add('POST', $path, $handler); }
    public function any(string $path, $handler): void  { $this->add('ANY',  $path, $handler); }

    private function add(string $m, string $p, $h): void {
        $this->routes[] = ['method'=>$m, 'path'=>trim($p,'/'), 'handler'=>$h];
    }

    public function dispatch(): void {
        $url    = $this->currentPath();
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        foreach ($this->routes as $r) {
            $params = $this->match($r['path'], $url);
            if ($params === false) continue;
            if ($r['method'] !== 'ANY' && $r['method'] !== $method) continue;
            $this->call($r['handler'], $params);
            return;
        }
        $this->abort404();
    }

    private function currentPath(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rawurldecode($path);

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir  = trim(dirname($scriptName), '/');
        if ($scriptDir !== '' && trim($path, '/') === $scriptDir) {
            $path = '';
        } elseif ($scriptDir !== '' && str_starts_with(trim($path, '/'), $scriptDir . '/')) {
            $path = substr(trim($path, '/'), strlen($scriptDir) + 1);
        } else {
            $path = trim($path, '/');
        }

        if (str_starts_with($path, 'index.php/')) {
            $path = substr($path, strlen('index.php/'));
        } elseif ($path === 'index.php') {
            $path = '';
        }

        $appPath = trim((string) parse_url(defined('APP_URL') ? APP_URL : '', PHP_URL_PATH), '/');
        if ($appPath !== '' && str_starts_with($path, $appPath . '/')) {
            $path = substr($path, strlen($appPath) + 1);
        } elseif ($path === $appPath) {
            $path = '';
        }

        return trim($path, '/');
    }

    private function match(string $route, string $url): array|false {
        if ($route === $url) return [];
        $pat = '#^' . preg_replace('/\{[a-zA-Z_]+\}/', '([^/]+)', $route) . '$#u';
        if (preg_match($pat, $url, $m)) { array_shift($m); return $m; }
        return false;
    }

    private function call($handler, array $params): void {
        if (is_callable($handler)) { call_user_func_array($handler, $params); return; }
        [$cls, $method] = explode('@', $handler);
        if (!class_exists($cls)) { $this->abort404(); return; }
        $obj = new $cls();
        if (!method_exists($obj, $method)) { $this->abort404(); return; }
        call_user_func_array([$obj, $method], $params);
    }

    private function abort404(): void {
        http_response_code(404);
        $f = defined('APP_PATH') ? APP_PATH.'/Views/store/pages/404.php' : null;
        if ($f && file_exists($f)) { require $f; }
        else echo '<h1 style="font-family:sans-serif;text-align:center;margin-top:20vh;color:#6d5acd">404 — الصفحة غير موجودة</h1>';
    }
}
