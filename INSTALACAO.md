# 📱 Proxy Android - Sistema Completo

Sistema de proxy para Android com painel web de controle integrado.

## 🎯 Funcionalidades

✅ **Login Seguro**
- Usuário: `Proxy`
- Senha: `Android`

✅ **Painel Web Completo**
- Gerenciar múltiplos proxies
- Testar conexões
- Monitorar status online/offline
- Dashboard com estatísticas

✅ **Site de Download**
- Página profissional para download do APK
- FAQ completo
- Especificações técnicas
- Suporte a múltiplas versões

## 📂 Estrutura de Arquivos

```
├── index.php           # Página de login
├── painel.php          # Painel de controle
├── site.html           # Site de download
├── config.php          # Configurações globais
├── .htaccess           # Regras de segurança
├── proxies.json        # Dados de proxies (gerado automaticamente)
└── README.md           # Este arquivo
```

## 🚀 Como Usar

### 1. Acesso ao Painel
```
http://localhost:8888/index.php
```
- Login com: `Proxy` / `Android`

### 2. Site de Download
```
http://localhost:8888/site.html
```

### 3. Gerenciar Proxies
- Adicionar novo proxy (Host, Porta, Tipo)
- Testar conectividade
- Remover proxies
- Visualizar status em tempo real

## 🔒 Segurança

- ✅ Sessões PHP protegidas
- ✅ Validação de entrada de dados
- ✅ Proteção contra CSRF
- ✅ Headers de segurança configurados
- ✅ Arquivos sensíveis bloqueados

## 📋 Dados de Proxy

Cada proxy armazena:
- `id`: Identificador único
- `host`: Endereço IP ou hostname
- `porta`: Porta do proxy (1-65535)
- `usuario`: Usuário (opcional)
- `senha`: Senha (opcional)
- `tipo`: HTTP, HTTPS ou SOCKS5
- `status`: Online/Offline
- `data_criacao`: Timestamp de criação

## 🛠️ Requisitos

- PHP 7.4+
- Apache com mod_rewrite
- Suporte a JSON
- Permissão de escrita nas pastas

## 📝 Credenciais Padrão

**Painel Web:**
- Usuário: `Proxy`
- Senha: `Android`

## 🔄 Funcionalidades do Painel

1. **Dashboard**
   - Total de proxies
   - Proxies online
   - Proxies offline

2. **Adicionar Proxy**
   - Host/IP
   - Porta
   - Tipo (HTTP/HTTPS/SOCKS5)
   - Autenticação opcional

3. **Gerenciar Proxies**
   - Testar conectividade
   - Remover proxies
   - Visualizar detalhes

4. **Segurança**
   - Logout automático
   - Sessão com timeout

## 📱 Site de Download

Página responsiva com:
- Seção Hero com CTA
- Lista de recursos
- Especificações técnicas
- Versões disponíveis
- FAQ interativo
- Links úteis

## 🐛 Troubleshooting

**Problemas de acesso:**
1. Verificar PHP está instalado e ativo
2. Confirmar permissões de pasta
3. Limpar cache do navegador

**Proxies não salvam:**
1. Verificar permissão de escrita em `/data`
2. Confirmar espaço em disco
3. Verificar sintaxe JSON

## 📞 Suporte

Para reportar problemas:
1. Verifique se todas as pastas têm permissão
2. Confira os requisitos do servidor
3. Consulte os logs em `/logs`

## 📄 Licença

MIT License - Sinta-se livre para usar e modificar

---

**Versão:** 1.0.0  
**Última atualização:** 03/06/2026  
**Autor:** Proxy Team
