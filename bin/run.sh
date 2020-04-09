#!/bin/bash
docker run -it --rm -v "$PWD":/var/www/html -w /var/www/html php:5.6 php "$@"