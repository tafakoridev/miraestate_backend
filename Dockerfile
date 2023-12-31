# Use an official PHP runtime as a parent image
FROM php:8.1-apache

# Set the working directory to /var/www/html
WORKDIR /var/www/html

# Copy composer.lock and composer.json to install dependencies
COPY composer.lock composer.json /var/www/html/

# Install any needed packages
RUN apt-get update && \
    apt-get install -y git zip unzip && \
    docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Laravel dependencies
RUN composer install --no-scripts --no-interaction

# Copy the rest of the application code
COPY . /var/www/html/

# Set the appropriate permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Enable Apache modules
RUN a2enmod rewrite
RUN composer install
RUN chown www-data:www-data -R ./storage
# Expose port 80 and start Apache
EXPOSE 80
CMD ["apache2-foreground"]
