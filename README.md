# Symfony application

## How to run?

1. Checkout the repository.
   Modify the `.env` for custom mail from address APP_MAIL, payment api key REVOUT_SK, other params if needed
2. Run the commands:

```bash
docker-compose build --no-cache
docker-compose up -d

docker-compose exec php bash


composer install
bin/console doctrine:migrations:migrate

```

# After a while, you will be able to access the following URLs:

- http://localhost:8080 - The Symfony website

register:
http://localhost:8080/register

create new order:
http://localhost:8080/order

view order history on:
http://localhost:8080/dashboard