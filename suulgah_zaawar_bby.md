# 1. Clone the repository (if not already present)

git clone <your-repo-url>
cd urgiinmod

# 2. Install PHP dependencies

composer install

# 3. Install Frontend dependencies

npm install

# 4. Setup Environment File

# Copy the example file to create your local configuration

cp .env.example .env

# 5. Generate Application Key

php artisan key:generate

# 6. Database Setup

# Ensure your database file exists (for SQLite) or credentials are set in .env

touch database/database.sqlite
php artisan migrate

# 7. Run dev using composer (starts vite , laravel)

composer run dev
