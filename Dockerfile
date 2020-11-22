FROM php:7.3-cli

COPY . /src/lib
WORKDIR /src/lib

CMD [ "php", "./src/lib/run.php" ]
