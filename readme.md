# Codeigniter Template

Boilerplate Template for Codeigniter projects made by Web Imp. This template is based off the [Inspinia](http://webapplayers.com/inspinia_admin-v2.9.2/index.html) theme (v2.8.0), with Material Design theme.

## 1. Dependencies

Dependencies are managed by both composer and npm, each for different purposes.

### 1.1. Composer

To manage PHP packages or libraries in the project. Packages are defined in `composer.json`. You can find more packages to install at [packagist.org](https://packagist.org/).

To install the packages, run `composer install`. A `vendor` folder will be created with all the packages defined inside `composer.json`.

### 1.2. NPM

To manage front-end or development packages to compile front-end scripts and stylesheets. Packages are defined in `package.json`. You can find more packages to install at [npmjs.com](https://www.npmjs.com/).

To install the packages, run `npm install`. A `node_modules` folder will be created with all the packages defined inside `package.json`.

## 2. Database

Database configurations are found in `/application/config/database.php`. Inside, you'll see multiple configurations for different environments:

- production: the database used by the client
- staging: same environment as production but for testing purposes
- docker: auto-testing environment
- localhost: development purposes

Configure the `localhost` database settings for your localhost Development Environment.
