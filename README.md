ECR Thing
=========

### Building The Containers

```shell
$ ./build.sh

# For prod build
$ ./build.sh prod
```

### Running The Containers
```shell
$ docker-compose up
```

### Developing With The Containers
To link your local project into the running containers
```shell
# The first step only needs to be done once
$ cp docker-compose.override.yaml.dist docker-compose.override.yaml
$ docker-compose up
```

If you'd like to run the production containers, uncomment the two `image`
lines in `docker-compose.override.yaml` then
```shell
$ docker-compose up
```
