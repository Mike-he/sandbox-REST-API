# Sandbox REST API

#### Build Image

```bash
docker build -f Dockerfile_for_homestead -t rest-api:latest .
```

#### Run Container

```bash
docker run -it -d -v $(pwd):/var/www/sandbox-REST-API -p 10080:80 --name=rest-api rest-api:latest
```

#### Mysql Server

```bash
docker run -it -d -e MYSQL_ROOT_PASSWORD=root --name=mysql mysql:5.6
```

#### Check Container Ip Address

```bash
docker inspect mysql | grep "IPAddress"
```