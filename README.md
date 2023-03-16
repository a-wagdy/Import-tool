# Local Branx X Task

# Stack

- PHP 8.1.0
- Laravel 10
- MySQL 8
- Nginx

# Installation

- If you're on Windows or Mac, make sure to have the docker desktop app up and running.
- If you're on Linux, install docker and docker compose.

1. Navigate to the app's directory, and build the image by running `docker-compose build`. This will take few mintues.
2. Then run `docker-compose up -d`.
3. `docker-compose exec import composer install`

> By building the image, you should have the database automatically uploaded inside the container.

To make sure that the image is working, please open your browser and navigate to `http://127.0.0.1:8080/`.

# REST API

## Import the CSV file

- curl -X POST -H 'Content-Type: text/csv' --data-binary @import.csv http://127.0.0.1:8080/api/employee
> Please mind the `--data-binary`.

## Get one employee

- **[GET]**: http://127.0.0.1:8080/api/employee/{id}

## List all employees

- **[GET]**: http://127.0.0.1:8080/api/employee

## Delete employee

- **[DELETE]**: http://127.0.0.1:8080/api/employee/{id}
