# Development

## Docker

For development purposes, there are prepared docker containers, which you can run locally to develop. 
However, these containers are **NOT** used in production environment.

To start up the containers, run command in `/docker`:
  
    UID=${UID} docker-compose up

_If you start these containers for the first time, the application will get installed. 
If it isn't the case and something goes wrong, please, install the application using [this page](installation-application.md)._

To run commands, you need to enter the containers, using this command

    docker exec -it --user cocorico docker_cocorico_1 sh

## Mailcatcher

There is also a container for testing email receiving. The user interface is accessible on http://localhost:1080/

## Adminer

There is also a container to access the DB using [Adminer](https://www.adminer.org/en/). You can access adminer on http://localhost:8080/

To login, use credential:

        server: mysql
        user: cocorico
        pass: cocorico
    
        
    
