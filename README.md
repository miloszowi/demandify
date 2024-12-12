<p align="center"> 
<img src="https://github.com/miloszowi/querify/actions/workflows/app.yml/badge.svg" alt="pipeline status" />
</p>
<h1 align="center">Querify </h1>
<p align="center">Web application to serve production services demands.</p>


## Requirements
* PHP 8.4 or newer
* PostgreSQL 16 or newer
* RabbitMQ 3.*

## Quick Start
Clone the repository
```
$ git clone git@github.com:miloszowi/querify.git
```
run
```
$ docker/start
```
to setup everything at once - querify should be available on localhost.


## Running tests
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

for specific case, use one of those (inside a container):
```bash
$ composer phpspec
$ composer phpunit
$ composer phpstan
$ composer csfixer-check
$ composer deptrac
```

## OAuth providers
- Slack

## Naming Conventions
### Demand
Represents a request/query made to perform a specific action on an external service.

For example:
A user creates a **Demand** to execute `SELECT * FROM orders` on the External Service querify_postgres.

### ExternalService
Registered and configured service within the application (in environment variables) that facilitates specific actions or queries.
Each of the External Service has his ExternalServiceConfiguration where eligible approvers to accept/deny demands are specified.

For example:
A user initiates a Demand to run a query `SELECT * FROM orders` on the **External Service** querify_postgres.

### Task
Entity that tracks the execution and results of a Demand performed on an External Service. It contains all relevant details of the completed demand.

For example:
When a Demand to execute `SELECT * FROM orders` on querify_postgres is approved and executed, the **Task** entity stores its results and metadata.

### User
Represents an individual utilizing the application to create, manage, and track Demands and Tasks.

### UserSocialAccount
External social account linked to a User. It may be used for authentication, notifications, or communication about Demands, approvals, and other activities. A User can have multiple linked Social Accounts.

For example:
A User Social Account such as Slack profile can notify the user of a new Demand approval.