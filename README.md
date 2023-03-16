# Local Branx X Task

# Stack

- PHP 8.1.0
- Laravel 10
- MySQL 8
- Nginx

# Installation

- If you're on Linux, install docker and docker compose.
- If you're on Windows or Mac, make sure to have the docker desktop app up and running.

1. Clone: `git clone git@github.com:a-wagdy/lbx-2.git`.
2. Navigate to the app's directory
3. Build the image by running `docker-compose build`. This will take few mintues.
4. Then run `docker-compose up -d`.
5. `docker-compose exec import composer install`

**By building the image, you should have the database automatically uploaded inside the container.**

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


# DB structure

<img width="662" alt="Screenshot 2023-03-16 at 12 57 18 PM" src="https://user-images.githubusercontent.com/64163189/225596757-3f55315f-5073-494a-86f5-a6b7a7eca39e.png">

