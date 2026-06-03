<?php
// Configurações do Proxy Android

// Informações da aplicação
define('APP_NAME', 'Proxy Android');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Proxy Team');

// Configurações de segurança
define('LOGIN_USER', 'Proxy');
define('LOGIN_PASS', 'Android');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configurações do servidor
define('SERVER_HOST', $_SERVER['HTTP_HOST'] ?? 'localhost');
define('SERVER_PORT', 8888);

// Diretórios
define('BASE_PATH', dirname(__FILE__));
define('DATA_PATH', BASE_PATH . '/data');
define('LOGS_PATH', BASE_PATH . '/logs');

// Certificados SSL/TLS
define('SSL_CERT_PATH', BASE_PATH . '/mitmproxy-ca-cert.pem');
define('SSL_KEY_PATH', BASE_PATH . '/charles-ssl-proxying.pem');

// Função de log
function log_action($usuario, $acao, $detalhes = '') {
    $timestamp = date('Y-m-d H:i:s');
    $log_file = LOGS_PATH . '/proxy_' . date('Y-m-d') . '.log';
    
    // Criar pasta de logs se não existir
    if(!is_dir(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }
    
    $mensagem = "[{$timestamp}] Usuário: {$usuario} | Ação: {$acao} | Detalhes: {$detalhes}\n";
    file_put_contents($log_file, $mensagem, FILE_APPEND);
}

// Função de validação de segurança
function validar_sessao() {
    if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        return false;
    }
    
    if(time() - $_SESSION['timestamp'] > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    
    $_SESSION['timestamp'] = time();
    return true;
}

// Funções de proxy
class ProxyManager {
    private $arquivo_dados = 'proxies.json';
    
    public function __construct() {
        // Criar pasta de dados se não existir
        if(!is_dir(DATA_PATH)) {
            mkdir(DATA_PATH, 0755, true);
        }
    }
    
    public function get_all() {
        $caminho = DATA_PATH . '/' . $this->arquivo_dados;
        if(file_exists($caminho)) {
            return json_decode(file_get_contents($caminho), true) ?? [];
        }
        return [];
    }
    
    public function add($dados) {
        $proxy = [
            'id' => uniqid('proxy_'),
            'host' => $dados['host'] ?? '',
            'porta' => $dados['porta'] ?? '',
            'usuario' => $dados['usuario'] ?? '',
            'senha' => $dados['senha'] ?? '',
            'tipo' => $dados['tipo'] ?? 'http',
            'status' => 'ativo',
            'data_criacao' => date('Y-m-d H:i:s')
        ];
        
        $proxies = $this->get_all();
        $proxies[] = $proxy;
        $this->save($proxies);
        
        return $proxy;
    }
    
    public function update($id, $dados) {
        $proxies = $this->get_all();
        foreach($proxies as &$p) {
            if($p['id'] === $id) {
                $p = array_merge($p, $dados);
                break;
            }
        }
        $this->save($proxies);
    }
    
    public function delete($id) {
        $proxies = $this->get_all();
        $proxies = array_filter($proxies, fn($p) => $p['id'] !== $id);
        $this->save(array_values($proxies));
    }
    
    public function get_by_id($id) {
        $proxies = $this->get_all();
        foreach($proxies as $p) {
            if($p['id'] === $id) {
                return $p;
            }
        }
        return null;
    }
    
    private function save($dados) {
        $caminho = DATA_PATH . '/' . $this->arquivo_dados;
        file_put_contents($caminho, json_encode($dados, JSON_PRETTY_PRINT));
    }
}

// Constante para usar em toda aplicação
$proxy_manager = new ProxyManager();
?>
