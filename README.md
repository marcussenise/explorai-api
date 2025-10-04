# explorai-api

/var/www/explorai-backend/
│
├── public/                 # 🌍 Pasta pública acessível pela web (ponto de entrada)
│   └── index.php           # Arquivo que recebe todas as requisições (ex: API REST)
│
├── src/                    # 🧠 Código-fonte principal (rotas, controladores, etc.)
│   ├── routes/             # Definição das rotas (ex: auth.php, users.php)
│   ├── controllers/        # Lógica de cada rota (AuthController.php, UserController.php)
│   ├── services/           # Serviços como FirebaseService, EmailService, etc.
│   ├── models/             # Modelos de dados (se usar MySQL)
│   ├── middlewares/        # Validação de token, autorização, etc.
│   └── helpers/            # Funções utilitárias
│
├── config/                 # ⚙️ Configurações gerais
│   ├── firebase.php        # Inicialização do Firebase Admin SDK
│   ├── database.php        # Configuração do banco MySQL (se houver)
│   └── env.php             # Variáveis de ambiente (ou carregar de .env)
│
├── storage/                # 📦 Logs, cache, uploads (não acessível publicamente)
│   ├── logs/
│   └── uploads/
│
├── vendor/                 # 📦 Pacotes instalados pelo Composer
│
├── .env                    # Variáveis sensíveis (Firebase keys, senhas, etc)
├── composer.json           # Dependências do projeto
└── README.md
