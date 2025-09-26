# 1) Build assets (Vite/Tailwind)
FROM node:20 AS assets
WORKDIR /app
COPY package*.json ./
# ถ้ามี lockfile ใช้ npm ci, ถ้าไม่มี fallback เป็น npm install
RUN if [ -f package-lock.json ]; then npm ci; else npm install --no-audit --no-fund; fi
COPY . .
RUN npm run build

# 2) PHP + Apache
FROM php:8.2-apache

ARG DEBIAN_FRONTEND=noninteractive

# Timezone + build deps + dev libs ที่จำเป็นต่อการคอมไพล์ ext ต่างๆ
RUN apt-get update && apt-get install -y \
    tzdata git unzip pkg-config \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libzip-dev libicu-dev libonig-dev \
 && ln -fs /usr/share/zoneinfo/Asia/Bangkok /etc/localtime \
 && dpkg-reconfigure -f noninteractive tzdata \
 && echo "date.timezone=Asia/Bangkok" > /usr/local/etc/php/conf.d/timezone.ini \
 # เปิด option ให้ gd รองรับ jpeg/freetype
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 # คอมไพล์ php extensions (มี -j$(nproc) ให้เร็วขึ้น)
 && docker-php-ext-install -j$(nproc) pdo_mysql gd zip intl opcache mbstring \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# Apache docroot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
 && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
# คัดลอกโค้ด + ไฟล์ build จาก stage assets
COPY . .
COPY --from=assets /app/public/build ./public/build

# Entrypoint
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
