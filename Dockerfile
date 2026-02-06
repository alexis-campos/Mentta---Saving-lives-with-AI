FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions with proper MySQL 8 caching_sha2_password support
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable OpenSSL for caching_sha2_password
RUN docker-php-ext-enable pdo_mysql

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Expose port (Railway sets $PORT)
EXPOSE 8080

# Start PHP built-in server
CMD php -S 0.0.0.0:${PORT:-8080} -t .
