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

# Installer les outils nécessaires
RUN apt-get update && apt-get install -y \
    curl gnupg

# Ajouter le dépôt officiel Yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

# Installer Yarn
RUN apt update && apt install npm
RUN apt-get update && apt-get install -y yarn