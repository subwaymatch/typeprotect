# TypeProtect demo — runnable on any container host (Render, Railway, Fly.io…).
FROM php:8.3-cli-alpine

WORKDIR /var/www/html
COPY . /var/www/html/

# Most free hosts inject the port to bind on via $PORT (Render defaults to 10000).
ENV PORT=8080
EXPOSE 8080

# Built-in server is plenty for a demo; use php-fpm + nginx/apache for real traffic.
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /var/www/html"]
