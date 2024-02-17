# Import CSV file

# Stack

- PHP 8.1.0
- Laravel 10
- MySQL 8
- Nginx
- Redis

# Installation

- If you're on Linux, install docker and docker compose.
- If you're on Windows or Mac, make sure to have the docker desktop app up and running.

1. Clone the repo.
2. Navigate to the app's directory
3. Build the image by running `docker-compose build`. This will take a few minutes.
4. Then run `docker-compose up -d`.
5. `docker-compose exec import composer install`

**By building the image, you should have the database automatically uploaded inside the container.**

To make sure that the image is working, please open your browser and navigate to `http://127.0.0.1:8080/`.

# REST API

In this repo, you'll find the [Postman Collection](https://github.com/a-wagdy/Import-tool/blob/main/Import%20Tool.postman_collection.json), and the [CSV file](https://github.com/a-wagdy/Import-tool/blob/main/import.csv).

## Import the CSV file

- **[POST]**: http://127.0.0.1:8080/api/import

  > Make sure to open the terminal and execute `docker-compose exec import php artisan queue:listen`

## Get one employee

- **[GET]**: http://127.0.0.1:8080/api/employee/{id}

## List all employees

- **[GET]**: http://127.0.0.1:8080/api/employees

## Delete employee

- **[DELETE]**: http://127.0.0.1:8080/api/employee/{id}


# DB structure

<img width="662" alt="Screenshot 2023-03-16 at 12 57 18 PM" src="https://user-images.githubusercontent.com/64163189/225596757-3f55315f-5073-494a-86f5-a6b7a7eca39e.png">

