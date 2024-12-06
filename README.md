# Querify
Web application to serve production services demands.

## Requirement
* PHP 8.3 or newer
* PostgreSQL 16 or newer
* ...
* #TODO

## Installation
```
$ git clone git@github.com:miloszowi/querify.git
```

## Run
To start the project, you simply execute script below:
```
$ docker/start
```

## Tests
Querify uses the following to analyse & test code:
- **phpspec** for unit testing
- **phpunit** for integration & functional tests
- **phpstan** for static code analysis
- **csfixer** for detecting code standards
- **deptrac** for architecture static code analysis

to run all of them at once:
```bash
$ docker/tests
```
or inside the container 
```bash
$ composer tests
```

for specific tool, use one of those (inside a container):
```bash
$ composer phpspec
$ composer phpunit
$ composer phpstan
$ composer csfixer-check
$ composer deptrac

```



