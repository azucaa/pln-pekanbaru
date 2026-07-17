FROM php:8.2-fpm-alpine

# Install Nginx and PHP extensions
RUN apk add --no-cache nginx && \
    docker-php-ext-install pdo pdo_mysql

# Copy nginx config
COPY nginx.conf /etc/nginx/http.d/default.conf

# Copy application files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port (Railway expects 8080)
EXPOSE 8080

# Start PHP-FPM and Nginx
CMD php-fpm -D && nginx -g 'daemon off;'
