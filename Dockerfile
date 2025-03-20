FROM php:7.4
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Postgre PDO
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# RUN pecl install xdebug-2.6.0alpha1
RUN pecl install xdebug-2.9.0 
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-install mysqli
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql   && docker-php-ext-install  pgsql
# COPY xdebug.ini /etc/php/7.2/apache2/conf.d/20-xdebug.ini
# RUN echo ';xdebug.scream=1' >> cxdebug.ini \
#     && echo 'xdebug.remote_enable=1' >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo 'xdebug.remote_autostart=1' >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo 'xdebug.remote_connect_back=1' >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo 'xdebug.remote_port=9008' >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo 'xdebug.remote_mode=req' >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo 'xdebug.remote_handler=dbgp' >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo 'xdebug.remote_log=/tmp/php5-xdebug.log'  >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo "xdebug.remote_host=192.168.18.11"  >> /usr/local/etc/php/conf.d/xdebug.ini
    
RUN echo "xdebug.remote_host=192.168.0.105"          >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini                           
RUN echo "xdebug.remote_port=9008"                    >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini                                   
RUN echo "xdebug.remote_autostart=1"                    >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini                                   
RUN echo "xdebug.remote_enable=on"                    >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini  
RUN echo "xdebug.force_display_errors=on"             >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini  
RUN echo "xdebug.remote_handler = dbgp"               >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini 
RUN echo "xdebug.remote_mode = req"                   >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini 
RUN echo "xdebug.remote_log=/tmp/xdebug.log"          >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini 



WORKDIR /var/www/html

RUN mkdir -p /var/www/bingototal/public/images/
RUN mkdir -p /var/www/lottingo/public/images/

RUN chmod -R 777 /var/www/bingototal/public/images/
RUN chmod -R 777 /var/www/lottingo/public/images/

CMD php -S 0.0.0.0:8082
