FROM wordpress:latest

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt update && apt install -y less

RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

RUN apt-get install -y nodejs npm
WORKDIR /var/www/html/wp-content/plugins/iot-for-wp/frontend
RUN npm install


RUN echo "alias wp='wp --allow-root'" >> ~/.bashrc
SHELL ["/bin/bash", "source ~/.bashrc"]