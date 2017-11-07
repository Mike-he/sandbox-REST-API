# Sandbox REST API

#### Install Docker APP

> Download | [Docker Community Edition for Mac](https://store.docker.com/editions/community/docker-ce-desktop-mac)

#### Build Image

```bash
docker build -f Dockerfile_for_homestead -t rest-api:latest .
```

#### Run Container

```bash
docker run -it -d -v $(pwd):/var/www/sandbox-REST-API -p 10080:80 --name=rest-api --restart=always rest-api:latest
```

#### Mysql Server

```bash
docker run -it -d -e MYSQL_ROOT_PASSWORD=root -p 13306:3306 --name=mysql --restart=always mysql:5.6
```

#### Check Container Ip Address

```bash
docker inspect mysql | grep "IPAddress"
```