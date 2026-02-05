FROM php:8.2-cli

WORKDIR /app

# System deps + Chromium for Browsershot/Puppeteer
RUN apt-get update && apt-get install -y \
    git curl unzip zip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    # PostgreSQL client libraries
    libpq-dev postgresql-client \
    # Freetype and JPEG for GD extension
    libfreetype6-dev libjpeg62-turbo-dev \
    chromium \
    fonts-liberation \
    libasound2 libatk-bridge2.0-0 libatk1.0-0 libc6 libcairo2 libcups2 \
    libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libglib2.0-0 \
    libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 \
    libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 \
    libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 \
    libxss1 libxtst6 \
 && rm -rf /var/lib/apt/lists/*

# Node.js 20 + npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get update && apt-get install -y nodejs \
 && node -v && npm -v \
 && rm -rf /var/lib/apt/lists/*

# Configure GD with freetype and jpeg support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app
COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Frontend build (optional if you need it)
RUN npm ci --include=optional && npm run build

# Recommended: tell Browsershot/Puppeteer where Chromium is
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV BROWSERSHOT_CHROMIUM_PATH=/usr/bin/chromium

# Start Laravel (Railway provides PORT)
CMD ["bash", "-lc", "php artisan config:cache || true; php artisan route:cache || true; php artisan view:cache || true; php artisan storage:link || true; unset PHP_CLI_SERVER_WORKERS; php artisan serve --host=0.0.0.0 --port=${PORT:-8000} --no-reload"]
