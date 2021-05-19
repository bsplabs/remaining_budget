FROM orsolin/docker-php-5.3-apache:latest

# RUN apt-get update && apt-get install libzip-dev curl php5-curl -y --force-yes
# RUN pecl install zip-1.14.0.tar
# RUN cp /usr/lib/php5/20131226/curl.so /usr/local/lib/php/extensions/no-debug-non-zts-20090626/

# Clear cache
# RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# RUN a2enmod rewrite
# RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# RUN mkdir -p /etc/apache2/ssl

# Assign permissions of the working directory to the www-data user
# ADD ./grandadmin /var/www/html
# RUN chown -R www-data:www-data /var/www/html/*
# RUN chown -R 777 /var/www/html/*

# WORKDIR /var/www/html


# COPY ./docker/php/php.ini /usr/local/lib/
# COPY ./ssl/*.pem /etc/apache2/ssl/
# COPY ./ssl/cert.crt /etc/apache2/ssl/
# COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf
# RUN ln -sf /dev/stdout /var/log/apache2/access.log \
# 	&& ln -sf /dev/stderr /var/log/apache2/error.log

# EXPOSE 80
# EXPOSE 443
