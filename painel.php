<?php
session_start();

// Verifica se está logado
if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: index.php');
    exit;
}

// Arquivo de armazenamento de dados
$arquivo_dados = 'proxies.json';

// Carrega proxies salvos
$proxies = [];
if(file_exists($arquivo_dados)) {
    $proxies = json_decode(file_get_contents($arquivo_dados), true) ?? [];
}

// Função para salvar proxies
function salvar_proxies($data) {
    global $arquivo_dados;
    file_put_contents($arquivo_dados, json_encode($data, JSON_PRETTY_PRINT));
}

// Processa ações
$mensagem = '';
$tipo_msg = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    switch($acao) {
        case 'adicionar':
            $proxy = [
                'id' => uniqid(),
                'host' => $_POST['host'] ?? '',
                'porta' => $_POST['porta'] ?? '',
                'usuario' => $_POST['proxy_usuario'] ?? '',
                'senha' => $_POST['proxy_senha'] ?? '',
                'tipo' => $_POST['tipo'] ?? 'http',
                'status' => 'ativo',
                'data_criacao' => date('Y-m-d H:i:s')
            ];
            
            if(!empty($proxy['host']) && !empty($proxy['porta'])) {
                $proxies[] = $proxy;
                salvar_proxies($proxies);
                $mensagem = '✅ Proxy adicionado com sucesso!';
                $tipo_msg = 'sucesso';
            } else {
                $mensagem = '❌ Host e porta são obrigatórios!';
                $tipo_msg = 'erro';
            }
            break;
        
        case 'remover':
            $id_remover = $_POST['id'] ?? '';
            $proxies = array_filter($proxies, function($p) use ($id_remover) {
                return $p['id'] !== $id_remover;
            });
            $proxies = array_values($proxies);
            salvar_proxies($proxies);
            $mensagem = '✅ Proxy removido com sucesso!';
            $tipo_msg = 'sucesso';
            break;
        
        case 'editar':
            $id_editar = $_POST['id'] ?? '';
            foreach($proxies as &$p) {
                if($p['id'] === $id_editar) {
                    $p['host'] = $_POST['host'] ?? $p['host'];
                    $p['porta'] = $_POST['porta'] ?? $p['porta'];
                    $p['usuario'] = $_POST['proxy_usuario'] ?? $p['usuario'];
                    $p['senha'] = $_POST['proxy_senha'] ?? $p['senha'];
                    $p['tipo'] = $_POST['tipo'] ?? $p['tipo'];
                    break;
                }
            }
            salvar_proxies($proxies);
            $mensagem = '✅ Proxy atualizado com sucesso!';
            $tipo_msg = 'sucesso';
            break;
        
        case 'testar':
            $id_testar = $_POST['id'] ?? '';
            foreach($proxies as &$p) {
                if($p['id'] === $id_testar) {
                    // Simula teste de conexão
                    $conectado = @fsockopen($p['host'], $p['porta'], $errno, $errstr, 2);
                    if($conectado) {
                        $p['status'] = 'online';
                        fclose($conectado);
                        $mensagem = '✅ Proxy testado e online!';
                    } else {
                        $p['status'] = 'offline';
                        $mensagem = '⚠️ Proxy offline ou inacessível!';
                    }
                    $tipo_msg = $p['status'] == 'online' ? 'sucesso' : 'aviso';
                    break;
                }
            }
            salvar_proxies($proxies);
            break;
        
        case 'logout':
            session_destroy();
            header('Location: index.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Proxy Android</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .user-info {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .mensagem {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }
        
        .sucesso {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .erro {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .aviso {
            background: #ffe;
            color: #cc3;
            border-left: 4px solid #cc3;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 600;
            font-size: 13px;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            font-family: inherit;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-danger {
            background: #ff6b6b;
            color: white;
            font-size: 12px;
            padding: 6px 12px;
        }
        
        .btn-danger:hover {
            background: #ee5a52;
        }
        
        .btn-test {
            background: #51cf66;
            color: white;
            font-size: 12px;
            padding: 6px 12px;
        }
        
        .btn-test:hover {
            background: #40c057;
        }
        
        .proxies-list {
            display: grid;
            gap: 15px;
        }
        
        .proxy-card {
            background: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .proxy-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }
        
        .proxy-info {
            flex: 1;
        }
        
        .proxy-host {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .proxy-details {
            font-size: 12px;
            color: #666;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }
        
        .detail-item {
            display: flex;
            gap: 5px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #999;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .status-online {
            background: #d4edda;
            color: #155724;
        }
        
        .status-offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-ativo {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .proxy-actions {
            display: flex;
            gap: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📱 Proxy Android - Painel</h1>
        <div class="header-info">
            <div class="user-info">
                👤 Usuário: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
            </div>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="acao" value="logout">
                <button type="submit" class="btn-logout">Sair</button>
            </form>
        </div>
    </div>
    
    <div class="container">
        <?php if($mensagem): ?>
            <div class="mensagem <?php echo $tipo_msg; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        
        <!-- Estatísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Total de Proxies</div>
                <div class="stat-number"><?php echo count($proxies); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Proxies Online</div>
                <div class="stat-number" style="color: #51cf66;">
                    <?php echo count(array_filter($proxies, fn($p) => $p['status'] === 'online')); ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Proxies Offline</div>
                <div class="stat-number" style="color: #ff6b6b;">
                    <?php echo count(array_filter($proxies, fn($p) => $p['status'] === 'offline')); ?>
                </div>
            </div>
        </div>
        
        <!-- Adicionar Novo Proxy -->
        <div class="section">
            <h2>➕ Adicionar Novo Proxy</h2>
            <form method="POST">
                <input type="hidden" name="acao" value="adicionar">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Host/IP *</label>
                        <input type="text" name="host" placeholder="192.168.1.1" required>
                    </div>
                    <div class="form-group">
                        <label>Porta *</label>
                        <input type="number" name="porta" placeholder="8080" min="1" max="65535" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo">
                            <option value="http">HTTP</option>
                            <option value="https">HTTPS</option>
                            <option value="socks5">SOCKS5</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Usuário (opcional)</label>
                        <input type="text" name="proxy_usuario" placeholder="usuário">
                    </div>
                    <div class="form-group">
                        <label>Senha (opcional)</label>
                        <input type="password" name="proxy_senha" placeholder="senha">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Adicionar Proxy</button>
            </form>
        </div>
        
        <!-- Lista de Proxies -->
        <div class="section">
            <h2>📋 Proxies Configurados</h2>
            
            <?php if(empty($proxies)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🚫</div>
                    <p>Nenhum proxy configurado</p>
                    <p style="font-size: 12px; margin-top: 10px;">Adicione um novo proxy acima para começar</p>
                </div>
            <?php else: ?>
                <div class="proxies-list">
                    <?php foreach($proxies as $proxy): ?>
                        <div class="proxy-card">
                            <div class="proxy-info">
                                <div class="proxy-host">
                                    <?php echo htmlspecialchars($proxy['host']); ?>:<?php echo htmlspecialchars($proxy['porta']); ?>
                                </div>
                                <div class="proxy-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Tipo:</span>
                                        <span><?php echo strtoupper($proxy['tipo']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Criado:</span>
                                        <span><?php echo $proxy['data_criacao']; ?></span>
                                    </div>
                                    <?php if(!empty($proxy['usuario'])): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Auth:</span>
                                            <span><?php echo htmlspecialchars($proxy['usuario']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span class="status-badge status-<?php echo $proxy['status']; ?>">
                                    <?php echo $proxy['status'] === 'online' ? '🟢 Online' : '🔴 Offline'; ?>
                                </span>
                                
                                <div class="proxy-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="acao" value="testar">
                                        <input type="hidden" name="id" value="<?php echo $proxy['id']; ?>">
                                        <button type="submit" class="btn btn-test">Testar</button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja remover este proxy?');">
                                        <input type="hidden" name="acao" value="remover">
                                        <input type="hidden" name="id" value="<?php echo $proxy['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Remover</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
