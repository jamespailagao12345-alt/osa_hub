# syntax=docker/dockerfile:1

FROM dunglas/frankenphp:php8.2-bookworm

# Install system dependencies, Node.js 22, and Composer
RUN apt-get update \
    && apt-get install -y curl gnupg unzip \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN install-php-extensions ctype curl dom fileinfo filter hash mbstring \
    openssl pcre pdo_mysql pdo_pgsql session tokenizer xml gd zip

WORKDIR /app

# Copy everything first so artisan and other files are present during composer scripts
COPY . ./

# Install dependencies
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Build frontend assets (Laravel serves them from `public/build` by default)
RUN npm ci
RUN npm run build

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
