# Development

## Docker

For development purposes, there are prepared docker containers, which you can run locally to develop. 
However, these containers are NOT used in production environment.

To start up the containers, run command in `/docker`:
  
    UID=${UID} docker-compose up

To enter the containers, run 

    docker exec -it --user cocorico docker_cocorico_1 sh

## Mailcatcher

There is also a container for testing email receiving. The user interface is accessible on http://localhost:1080/
    
        
    
