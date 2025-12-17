# ===== Stage 1: Dependências Composer =====
FROM php:8.3-cli AS vendor

# Instalar dependências necessárias para Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensão zip do PHP (necessária para Composer)
RUN docker-php-ext-install zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

# ===== Stage 2: PHP-FPM + Nginx + Supervisor =====
FROM php:8.3-fpm

WORKDIR /var/www/html

# Instalar dependências do sistema e Nginx + Supervisor
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    zip \
    intl \
    opcache \
    bcmath \
    gd

# Instalar extensão Redis via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Copiar vendor da etapa anterior
COPY --from=vendor /app/vendor ./vendor

# Copiar o restante do código da aplicação
COPY . .

# Configurações PHP para produção
RUN { \
      echo "display_errors=Off"; \
      echo "display_startup_errors=Off"; \
      echo "log_errors=On"; \
      echo "error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT"; \
      echo "memory_limit=256M"; \
      echo "post_max_size=100M"; \
      echo "upload_max_filesize=100M"; \
      echo "max_execution_time=60"; \
      echo "date.timezone=America/Sao_Paulo"; \
    } > /usr/local/etc/php/conf.d/99-laravel.ini \
    && { \
      echo "opcache.enable=1"; \
      echo "opcache.enable_cli=1"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.interned_strings_buffer=16"; \
      echo "opcache.max_accelerated_files=10000"; \
      echo "opcache.validate_timestamps=0"; \
      echo "opcache.save_comments=1"; \
      echo "opcache.fast_shutdown=1"; \
    } > /usr/local/etc/php/conf.d/99-opcache.ini

# Copiar configurações
COPY deployment/nginx.conf /etc/nginx/sites-available/default
COPY deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Ajustar permissões
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Remover configuração padrão do Nginx
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Criar diretórios necessários para Supervisor
RUN mkdir -p /var/log/supervisor

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]
