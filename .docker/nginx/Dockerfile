FROM nginx:alpine

RUN apk update && apk add bash

COPY nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html

EXPOSE 80