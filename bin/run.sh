#!/bin/bash
docker run -it --rm -v "$PWD":/var/www/html -w /var/www/html php:7.4 php "$@"