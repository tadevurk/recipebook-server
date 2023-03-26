# Vedat Türk 683343 / Web Development (2)
# Homemade Recipe Book Project / Server Side

Code Structure

This project follows the Model-View-Controller (MVC) pattern, with a routing system implemented to handle URL requests. The SQL script for the database is included in the project.
* utils (there is also dataset-recipe.json which was used as an external datasaet for ingredients)


It contains:
* NGINX webserver
* PHP FastCGI Process Manager with PDO MySQL support
* MariaDB (GPL MySQL fork)
* PHPMyAdmin

Models:
* Recipe
* Ingredient
* User
* Role
* Auth

## Installation

1. Install Docker Desktop on Windows or Mac, or Docker Engine on Linux.
1. Clone the project

## Usage

In a terminal, run:
```bash
docker-compose up
```

NGINX will now serve files in the app/public folder. Visit localhost in your browser to check.
PHPMyAdmin is accessible on localhost:8080

If you want to stop the containers, press Ctrl+C. 
Or run:
```bash
docker-compose down
```

# Editors:
username: vedatturk
password: 1234

username: biggiriccardo
password: 1234

username: carcarmegan
password: 1234

# Admins:

username: atalaynisan
password: 1234
