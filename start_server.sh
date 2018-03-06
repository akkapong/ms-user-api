#!/bin/sh

docker rm -f ms-user-api-mongo
docker rm -f ms-user-api-app
docker rm -f ms-user-api-swagger
docker-compose rm

docker-compose build mongo
docker-compose up -d mongo

docker-compose build swagger
docker-compose up -d swagger

docker-compose build app
docker-compose up -d app