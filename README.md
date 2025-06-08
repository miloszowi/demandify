<h1 align="center">
<img title="logo" alt="logo" src="/docs/logo.png" width="150" height="150" /><br>
<strong>Demandify</strong>
</h1>

<p align="center">Web application to serve production services demands.</p>

<p align="center"> 
<img src="https://github.com/miloszowi/demandify/actions/workflows/app.yml/badge.svg" alt="pipeline status" />
<a href="https://codecov.io/gh/miloszowi/demandify" >
<img src="https://codecov.io/gh/miloszowi/demandify/graph/badge.svg?token=5CZC0FLC1V"/>
</a><img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="license" />
<img src="https://img.shields.io/github/stars/miloszowi/demandify" alt="repo stars">
</p>

# Table of Contents
- [About](#about)
  - [How it works](#how-it-works)
- [Security](#security)
  - [Public Endpoints](#public-endpoints)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Tests](#tests)
- [License](./LICENSE.md)
- [Naming Conventions](docs/naming_conventions.md)

## About
**Demandify** is a web application that allows for users to demand execution of query on specific instance (e.g mysql, postgres, redis) – all within a controlled and auditable workflow.

### How it works
1. **Users** can submit a **_demand_**, which includes:
   - `content` – the command to be executed (e.g., `SELECT * FROM users`, `FLUSHDB`, etc.)
   - `reason`
   - `destination` – instance to be run on (e.g `mysql_master-prod`)

2. Each demand goes through a **review process**:
   - Only users with the appropriate permissions (assigned by an **admin**) can **approve** or **reject** demands.

3. Upon **approval**, the command is executed automatically:
   - The **output** is saved to a file and made available to the requester.
   - If execution fails, the **error output** is captured and stored in the system.

## Security
When running Demandify in a production or internal organizational environment, **it is strongly recommended to**:
- Deploy the application behind a VPN or reverse proxy with Active Directory (AD) or SSO integration
- Limit public access only to trusted IP ranges
- Use HTTPS for all connections

This helps protect internal tooling, sensitive operational data and database connections from unauthorized access.

### Public Endpoints
The following endpoints must remain publicly accessible for integrations such as webhooks:
- `/webhook/{type}`

## Requirements
* PHP 8.4 or newer
* PostgreSQL 16 or newer
* Redis 6.2 or newer
* RabbitMQ 3.*

## Quick Start
Clone the repository & copy env file
```shell
$ git clone git@github.com:miloszowi/demandify.git
$ cd demandify
$ cp .env .env.local
```
fill out .env.local with environment variables and then run
```shell
$ docker/start
```
to set up everything at once - demandify should be available on localhost.

## Tests
Demandify uses the following to analyze and test code:
- **phpunit** for unit, integration and functional tests
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
$ composer phpunit
$ composer phpstan
$ composer csfixer-check
$ composer deptrac
```