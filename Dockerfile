# Use an official PHP runtime as a parent image
FROM php:8.1-fpm

# Set the working directory to /var/www
WORKDIR /var/www

# Copy composer.lock and composer.json to the working directory
COPY composer.lock composer.json /var/www/

# Install any needed packages specified in composer.lock
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Laravel dependencies
RUN composer install --no-scripts --no-autoloader

# Copy the application files to the container
COPY . /var/www

# Generate the autoload files and optimize
RUN composer dump-autoload --optimize

# Set up permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
