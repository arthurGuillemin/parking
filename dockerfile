FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copy composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install PHP dependencies (prod)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
