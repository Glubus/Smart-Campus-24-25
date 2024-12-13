FROM forge-registry.iut-larochelle.fr/php-fpm-composer/iutlr-info-php8.2-fpm-composer2

# Login to container as root user
USER root

# Symfony dev environement 
ENV APP_ENV=dev

# Copy php default configuration
COPY ./build/sfapp/conf/default.ini /usr/local/etc/php/conf.d/default.ini

# Set working directory
WORKDIR /app/sfapp

# Arguments defined in compose.yml
ARG USER_NAME
ARG USER_ID
ARG GROUP_NAME
ARG GROUP_ID

# Create system user to run Composer and PHP Commands
RUN if [ ! -z 0 ] && [ ! -z 0 ] && [ 0 -ne 0 ] && [ 0 -ne 0 ]; then \
    useradd -G www-data,root -u 0 -d /home/0 0 && \
    mkdir -p /home/0/.composer && \
    chown -Rf 0:0 /home/0 && \
    chown -R 0 /home/0; \
fi


# Login to container as non-root user 
USER ${USER_ID:-0}:${GROUP_ID:-0}