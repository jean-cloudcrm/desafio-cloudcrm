# Projeto em Laravel

# Primeiro clone o repositório localmente:
    ```bash;
    https://github.com/jean-cloudcrm/desafio-cloudcrm.git;
    cd example-app (mantido o nome original ao criar o projeto com "composer create-project laravel/laravel example-app");

# Instale as Dependências:
    composer install;
    npm install;

# Crie o arquivo .env:
    cp .env.example .env

# Configuração de Banco de dados usado para o arquivo .env:
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=cadastro-usuario
    DB_USERNAME=postgres
    DB_PASSWORD=1234

# Migration para criar as tabelas do banco de dados:
    php artisan migrate

# Inicialização do projeto por terminal:
    php artisan serve

# Documentação da API no formato insomnia:
    1. Baixe o arquivo [Insomnia_2024-09-26.json](./docs/Insomnia_2024-09-26.json).
    2. No Insomnia, clique no menu no canto superior esquerdo (três linhas horizontais).
    3. Selecione **Import/Export**.
    4. Clique em **Import Data** e depois em **From File**.
    5. Selecione o arquivo `Insomnia_2024-09-26.json` baixado.
    6. Todas as requisições estarão disponíveis no Insomnia.



