# TB API

A FHIR-based backend API application that connects to the cPRO data model.

## Setup
(see https://docs.docker.com/compose/gettingstarted/)

1. Install Docker and Docker compose.
2.  Clone project into desired working directory:
    ```
    git clone https://github.com/uwcirg/tb-api.git
    ```
3.  Build and run the containers
    ```
    cd tb-api
    docker-compose up -d --build
    ```

4. The following `localhost` ports will be used for the respective apps (see docker-compose files): 
    * 8080: Legacy cPRO application
    * 3060: TB Client - the responsive React web app
    * 5060: TB API - the Flask backend app
  
Navigate to http://localhost:5060/hello. You should get an `Welcome to TB API!` message.

## Debugging / Playing Around
The database is created using the `initdb.sql` file in the `/db/sql/` directory. The database container uses the volume `tbapi-db` for database data. It also maps a 2nd volume for initializing the database, which only happens if the main volume does not exist yet (I think).

The database container also exposes the mysql port (3306) as port 6603 externally, if enabled in the `docker-compose.yml` file.

To (1) re-create the db, (2) re-load it with data from initdb.sql, and (3) connect to it from the host machine (command-line mysql client or workbench):

1. Make sure the `6603` external port is mapped in the `docker-compose.yml` file. You can choose any port number you want that's unused.
2.  
            ```
            cd tb-api
            docker-compose down -v
            docker-compose up -d --build 

            mysql -u root -p root -h localhost -P 6603
            ```

`docker-compose down` removes all related volumes, networks, and containers, so we start at a clean slate.

`docker-compose up -d --build db` builds and starts only the db container.

## Development
Common commands:
```
cd <project_dir>

# Re-build and re-start containers, including db init:
docker-compose down -v
docker-compose up -d --build

# Windows-specific: Ensures container is notified of changes by Host inside volume
# Run this command in PowerShell
# See: https://github.com/merofeev/docker-windows-volume-watcher
Start-Process -NoNewWindow docker-volume-watcher mysite

# Start interactive shell connected to dev container
docker exec -it mpower-app bash

# Run flask shell inside interactive shell
> flask shell
```
