FROM php:7.1-cli

RUN  docker-php-ext-install pcntl && \
     docker-php-ext-install sockets

WORKDIR /var/app

ENTRYPOINT ["./bin/bracket.php", "g", "daemon off"]