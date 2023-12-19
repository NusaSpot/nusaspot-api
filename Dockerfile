FROM php:8.1-fpm-alpine

RUN apk --update --no-cache add \
    nginx \
    wget \
    libzip-dev \
    unzip \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && docker-php-ext-install zip pdo_mysql \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN echo "upload_max_filesize = 15M" >> /usr/local/etc/php/php.ini \
&& echo "post_max_size = 15M" >> /usr/local/etc/php/php.ini
# Install nginx and other dependencies
RUN apk add --no-cache nginx wget

# Create nginx runtime directory
RUN mkdir -p /run/nginx

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Create the application directory
RUN mkdir -p /app

# Copy the Laravel application files to the container
COPY . /app

# Download and install Composer
RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"

# Install PHP dependencies
RUN cd /app && /usr/local/bin/composer install --no-dev

# Set ownership of the application files
RUN chown -R www-data: /app

# Copy the startup script into the container
COPY startup.sh /app/startup.sh

# Grant execution permissions to the startup script
RUN chmod +x /app/startup.sh

# Expose the port
EXPOSE 9000

# Set the working directory
WORKDIR /app

# Set the environment variable for PHP
ENV PORT=9000

# Update the nginx configuration with the correct port
CMD sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf && /app/startup.sh
