<p align="center">
  <img src="assets/images/logo-epiguard.png" alt="epiGuard Logo" width="120" />
</p>

<h1 align="center">epiGuard</h1>

<p align="center">
  <strong>Sistema Inteligente de Gestão e Monitoramento de EPIs</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+" />
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License" />
  <img src="https://img.shields.io/badge/Status-Em%20Desenvolvimento-orange?style=for-the-badge" alt="Status" />
</p>

<p align="center">
  <a href="#-sobre">Sobre</a> •
  <a href="#-demonstração">Demonstração</a> •
  <a href="#-funcionalidades">Funcionalidades</a> •
  <a href="#-tecnologias">Tecnologias</a> •
  <a href="#-arquitetura">Arquitetura</a> •
  <a href="#-pré-requisitos">Pré-requisitos</a> •
  <a href="#-instalação">Instalação</a> •
  <a href="#-uso">Uso</a> •
  <a href="#-estrutura-do-projeto">Estrutura</a> •
  <a href="#-contribuindo">Contribuindo</a> •
  <a href="#-autor">Autor</a> •
  <a href="#-licença">Licença</a>
</p>

---

## 📋 Sobre

O **epiGuard** é uma plataforma web projetada para **gestão e monitoramento de Equipamentos de Proteção Individual (EPI)** em ambientes industriais e educacionais. O sistema centraliza o controle de conformidade de EPIs, permitindo que supervisores e gerentes de segurança registrem infrações, apliquem ações disciplinares escalonadas e acompanhem métricas de segurança em tempo real através de um dashboard analítico.

Desenvolvido com **Clean Architecture** em PHP puro (sem framework), o epiGuard prioriza separação de responsabilidades, testabilidade e manutenibilidade do código.

---

## 🎬 Demonstração

<!-- Adicione screenshots ou GIFs do sistema aqui -->
<p align="center">
  <em>Screenshots em breve...</em>
</p>

---

## ✨ Funcionalidades

### Dashboard Analítico
- 📊 KPIs em tempo real (infrações diárias, semanais, mensais)
- 📈 Gráficos interativos (barras, rosca, linha) com **Chart.js**
- 📅 Calendário de ocorrências com modal detalhado
- 🔍 Filtros por setor e período

### Sistema de Notificações em Tempo Real
- 🔔 Polling AJAX a cada 5 segundos para detecção de novas infrações
- 🃏 Toast notifications com design **glassmorphism** e auto-dismiss (3s)
- 🔢 Badge acumulativo no ícone do sino com persistência via `localStorage`
- 🔄 Reset automático do contador ao acessar a página de infrações

### Gestão de Infrações
- 👁️ Dupla visualização: **Tabela** clássica ou **Cards** com fotos
- 📝 Ações rápidas: Visualizar, Salvar, Assinar Ocorrência, Resolver
- 🔎 Filtros reativos por período, status, tipo de EPI e modo de exibição
- 📂 Exportação com seleção de setor e funcionários (PDF/Excel)

### Gestão Organizacional
- 🏢 CRUD completo de **Setores** com lista de EPIs obrigatórios
- 👷 Gestão de **Funcionários** com fotos e status de conformidade
- 👨‍🏫 Gestão de **Instrutores** e supervisores

### Autenticação e Segurança
- 🔐 Login seguro com hash **bcrypt**
- 👥 Perfis de acesso: Super Admin, Supervisor, Gerente de Segurança
- 🔑 Recuperação de senha
- 🛡️ Proteção contra SQL Injection (prepared statements)

---

## 🛠 Tecnologias

### Backend
| Requisito | Versão | Descrição |
| :--- | :--- | :--- |
| **PHP** | 8.1+ | Versão mínima recomendada para segurança e performance |
| **PostgreSQL** | 14+ | Banco de dados relacional para persistência |
| **PDO (pgsql)** | Nativo | Driver de conexão ao banco |
| **Apache/Nginx** | Recente | Servidor web para hospedar a aplicação |

### Frontend
| Tecnologia | Versão | Propósito |
|---|---|---|
| **JavaScript** | ES6+ | Lógica do cliente (AJAX, Chart.js, polling) |
| **CSS3** | — | Estilização (glassmorphism, animações, variáveis) |
| **HTML5** | — | Estrutura semântica |
| **Chart.js** | Latest | Gráficos analíticos |
| **Font Awesome** | 6.4 | Iconografia principal |
| **Lucide Icons** | Latest | Ícones complementares |
| **Google Fonts** | — | Inter + Outfit |

### Ferramentas
| Ferramenta | Propósito |
|---|---|
| **Composer** | Autoloader PSR-4 |
| **XAMPP** | Ambiente de desenvolvimento |
| **Git** | Controle de versão |

---

## 🏗 Arquitetura

O projeto segue a **Clean Architecture** com 4 camadas bem definidas:

```
┌─────────────────────────────────────────────────┐
│                 PRESENTATION                     │
│  Controllers • Views (PHP/HTML) • Middleware     │
├─────────────────────────────────────────────────┤
│                  APPLICATION                     │
│        Services • DTOs • Validators              │
├─────────────────────────────────────────────────┤
│                    DOMAIN                        │
│  Entities • Value Objects • Repository Interfaces│
├─────────────────────────────────────────────────┤
│                INFRASTRUCTURE                    │
│  MySQL Repositories • Database • Security        │
└─────────────────────────────────────────────────┘
```

### Banco de Dados (9 tabelas)

```
setores ─────┬──── funcionarios ──── ocorrencias ──── ocorrencia_epis ──── epis
             │                           │
             │                           ├──── acoes_ocorrencia ──── usuarios
             │                           ├──── evidencias
             └──── usuarios              └──── amostras_faciais
```

---

## 📦 Pré-requisitos

Antes de começar, verifique se você possui as seguintes ferramentas instaladas:

| Requisito | Versão Mínima |
|---|---|
| **PHP** | 8.1 |
| **MySQL** / **MariaDB** | 8.0 / 10.3 |
| **Apache** | 2.4 (com `mod_rewrite` habilitado) |
| **Composer** | 2.0 (opcional, autoloader manual disponível) |
| **XAMPP** | 8.1+ (alternativa simplificada) |

---

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/Arth-t-carvalho/epi-Guard.git
cd epi-Guard
```

### 2. Configure o ambiente

Copie o arquivo de exemplo e edite com suas credenciais:

```bash
cp config/.env.example config/.env
```

Edite `config/.env`:

```env
DB_HOST=localhost
DB_NAME=epi_guard
DB_USER=root
DB_PASS=
DB_PORT=3306
```

### 3. Crie o banco de dados

Importe o schema via terminal ou phpMyAdmin:

```bash
mysql -u root -p < database/schema.sql
```

Ou acesse `http://localhost/phpmyadmin`, crie o banco `epi_guard` e importe o arquivo `database/schema.sql`.

### 4. Instale as dependências (opcional)

```bash
composer install
```

> **Nota:** O sistema possui um autoloader PSR-4 manual embutido no `index.php`. O Composer é opcional.

### 5. Configure o servidor

Se estiver usando **XAMPP**, coloque o projeto em `C:\xampp\htdocs\` e acesse pelo navegador.

Se estiver usando o servidor embutido do PHP:

```bash
php -S localhost:8080 -t .
```

---

## 💻 Uso

### Acesso ao sistema

1. Acesse `http://localhost/trabalhos/epiGuard/login`
2. Cadastre um novo usuário em `http://localhost/trabalhos/epiGuard/register`
3. Faça login com as credenciais criadas

### Páginas principais

| Rota | Descrição |
|---|---|
| `/dashboard` | Dashboard analítico com KPIs e gráficos |
| `/infractions` | Gestão de infrações (Tabela/Cards) |
| `/monitoring` | Monitoramento em tempo real |
| `/occurrences` | Registro de ocorrências |
| `/management/departments` | Gestão de setores |
| `/management/employees` | Gestão de funcionários |

### APIs disponíveis

```bash
# Dados para gráficos
GET /api/charts

# Polling de novas infrações
GET /api/check_notificacoes?last_id=0

# Ocorrências por data
GET /api/calendar?date=2026-03-26

# CRUD de setores
GET    /api/departments
POST   /api/departments/create
POST   /api/departments/update
POST   /api/departments/delete
```

---

## 📁 Estrutura do Projeto

```
epiGuard/
├── assets/
│   ├── css/
│   │   ├── auth.css              # Estilos de autenticação
│   │   ├── dashboard.css         # Estilos principais (~103KB)
│   │   ├── management.css        # Estilos de gestão
│   │   └── sidebar.css           # Estilos da sidebar
│   ├── images/                   # Imagens e avatares
│   └── js/
│       ├── auth.js               # Validação de formulários de auth
│       ├── dashboard.js          # Integração Chart.js (~37KB)
│       ├── infractions.js        # Lógica de filtros e export
│       └── notifications.js      # Motor de polling (5s)
│
├── config/
│   ├── .env                      # Variáveis de ambiente
│   ├── .env.example              # Template de configuração
│   ├── app.php                   # Configurações da aplicação
│   ├── database.php              # Configuração do banco
│   └── routes.php                # Mapa de rotas
│
├── database/
│   ├── migrations/               # Migrações de banco
│   ├── seeds/                    # Seeds de dados iniciais
│   └── schema.sql                # Schema completo (9 tabelas)
│
├── src/
│   ├── Domain/
│   │   ├── Entity/               # 8 entidades de domínio
│   │   ├── ValueObject/          # 6 value objects
│   │   ├── Repository/           # 6 interfaces de repositório
│   │   └── Exception/            # Exceções de domínio
│   │
│   ├── Application/
│   │   ├── DTO/                  # Data Transfer Objects
│   │   ├── Service/              # Serviços de aplicação
│   │   └── Validator/            # Validadores
│   │
│   ├── Infrastructure/
│   │   ├── Database/             # Singleton de conexão
│   │   ├── Persistence/          # 6 repositórios MySQL
│   │   ├── External/             # Integrações externas
│   │   ├── Security/             # Segurança
│   │   └── Storage/              # Armazenamento de arquivos
│   │
│   └── Presentation/
│       ├── Controller/           # 9 controllers + 7 API
│       ├── Middleware/            # Auth middleware
│       └── View/                 # 9 módulos de views
│
├── tests/                        # Testes automatizados
├── .htaccess                     # Rewrite rules
├── composer.json                 # Configuração Composer
├── index.php                     # Ponto de entrada + Router
└── README.md                     # Este arquivo
```

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Para contribuir:

1. Faça um **Fork** do projeto
2. Crie sua **branch** de feature:
   ```bash
   git checkout -b feature/minha-feature
   ```
3. Faça **commit** das alterações:
   ```bash
   git commit -m 'feat: adiciona minha feature'
   ```
4. Faça **push** para a branch:
   ```bash
   git push origin feature/minha-feature
   ```
5. Abra um **Pull Request**

### Convenções de Commit

| Prefixo | Uso |
|---|---|
| `feat:` | Nova funcionalidade |
| `fix:` | Correção de bug |
| `refactor:` | Refatoração de código |
| `docs:` | Documentação |
| `style:` | Formatação (sem alteração de lógica) |
| `test:` | Adição de testes |

---

## 👤 Autor

**Arthur Carvalho**

- GitHub: [@Arth-t-carvalho](https://github.com/Arth-t-carvalho)

---

## 📄 Licença

Este projeto está sob a licença **MIT**. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.

---

<p align="center">
  Feito com ❤️ por <strong>Arthur Carvalho</strong>
</p>
