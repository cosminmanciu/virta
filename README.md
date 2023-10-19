# virta

## Task
Task Description
Your task is to implement a Rest-API for our electric vehicle charging station management system.
Notes:
You are free to choose any kind of PHP framework (the recommendation is Laravel).
You are free to choose any kind of database that fits you the best.
You must use the provided database schema in your implementation. However, feel free to add/modify everything as needed.
Pay attention to the scalability of the API.
In the same GPS coordinates, you can find multiple stations that belong to different companies
One charging company can own one or more other charging companies.
Hence the parent company should have access to all its children companies' stations, hierarchically. For example, we have 3 companies A, B,
and C owning respectively 10, 5 and 2 charging stations. Company B belongs to A and company C belongs to B.
Then we can say that company A owns 17, company B owns 7, and company C owns 2 charging stations in total.
## Requirements:
- mysql:8.0.33
- php:8.2
- nginx:1.21

## Features
- Symfony App 6.3
- 2 related Entities, One for Companies and one for Stations
- 2 services : One for Company and one for Station
- 2 Unit Tests

## How to run the project

- Open git terminal
- `git clone https://github.com/cosminmanciu/virta.git`
- `cd .docker`
- `docker-compose up -d`
- Connect to Docker PHP container
- `composer install`
- `php bin/console doctrine:schema:update --force`
- Go to route http://127.0.0.1/doc
- `Select definitions (Company or Station) in the upper right side`
- `Execute CRUD calls for both entities`
- `Execute /stations/get to check Task 1 and Task 2 results`


- To run Unit Tests
- `php -d memory_limit=2048M vendor/bin/phpunit --no-coverage`