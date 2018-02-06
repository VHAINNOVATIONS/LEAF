# LEAF
The Light Electronic Action Framework (LEAF) empowers VA staff in process improvement. LEAF is a solution that enables non-technical users to rapidly digitize paper processes, such as travel and tuition reimbursement, FTE, and many other types of resource requests.

## Repository Overview
* `LEAF_Nexus`
    * User account cache and user groups
    * Organizational Chart
* `LEAF_Request_Portal`
    * Electronic forms and workflow system
* `libs`
    * Third party libraries

## Installation

### Docker

[Docker](https://docker.com) is used to provide a consistent enviroment between developers, and eventually production.

### Configuration

Several files need to be created/updated for LEAF to operate in your environment.

In the sections below `$dbUser` and `$dbPass` are the same values used in the mysql Dockerfile and setup script.

#### Apache

Ensure that the apache server configuration contains:

```apache
RewriteEngine On
RewriteRule (.*)/api/(.*)$ $1/api/index.php?a=$2 [QSA,L]
```

This will fix some issues where the API endpoint is unreachable.

#### LEAF_Nexus
	
Rename `config-example.php` to `config.php` and change the following variables 
```php
$dbHost = 'mysql'
$dbName = 'leaf_users'
$dbUser = 'tester'
$dbPass = 'tester'
```

#### LEAF_Request_Portal 

Rename `db_config-example.php` to `db_config.php` and change the following variables:

```php
$dbHost = 'mysql'
$dbName = 'leaf_users'
$dbUser = 'tester'
$dbPass = 'tester'

$phonedbHost = 'mysql'
$phonedbName = 'leaf_users'
$phonedbUser = 'tester'
$phonedbPass = 'tester'	

# this should point to the LEAF_Nexus directory
$orgchartPath = '../LEAF_Nexus'
```

### Run

In the same directory as `docker-compose.yml` run: 

```bash
docker-compose up
```

Navigate to http://localhost/LEAF_Nexus or http://localhost/LEAF_Request_Portal in your browser.

### Contributing

#### Branches

Major branches:

* `master`: Contains the production ready code.
  * **NEVER** commit into `master`. **ONLY** pull requests from the `dev` branch and urgent hotfixes can be merged into `master`.
* `dev`: Contains finished code from the current sprint, ready for merging into `master` at any time.
  * **NEVER** commit into `dev`, create a pull request.

Typical workflow:

1. Start with the `dev` branch. Ensure `dev` is up to date on your machine.
1. Create a new feature branch from `dev` with the format `leaf###_short_feature_description`
    * Each branch name should begin with lowercase `leaf###` where `###` is the issue/ticket number and end with a short description of the feature. 
1. Do your work. Ensure proper tests are created (where applicable) and all existing tests pass.
1. Rebase the feature branch into as few logical commits as possible (1-3 total commits is ideal). 
1. Create a [good commit message](https://robots.thoughtbot.com/5-useful-tips-for-a-better-commit-message). Keep the commit subject under 50 characters, and wrap the commit message body at 72 characters.
1. Push feature branch to remote origin.
1. Create pull request for feature branch into `dev`.
1. Teammates will comment and/or approve the changes.
1. Make any necessary changes. 
1. Push changed feature branch to remote.
1. The pull request from the feature branch will be automatically updated.
1. After the pull request has been merged, the feature branch will be deleted.

## NOTICE

Within VA, LEAF is provided as a service (Software as a Service), and facilities are not responsible for procuring servers or installing software.

LEAF is currently not configured/optimized for usage outside of the VA, proper setup and authentication are responsiblities of the user.