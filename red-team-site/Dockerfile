FROM php:8.0-apache

# Install extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install network tools for Command Injection challenge
RUN apt-get update && apt-get install -y iputils-ping dnsutils whois && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy source code
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Update Apache config to allow .htaccess if needed
RUN echo '<Directory /var/www/html/> \n\
    Options Indexes FollowSymLinks \n\
    AllowOverride All \n\
    Require all granted \n\
    </Directory>' > /etc/apache2/conf-available/ctf.conf \
    && a2enconf ctf

EXPOSE 80
