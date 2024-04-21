
## Usage
This environment is set up with [DDEV]([https://ddev.readthedocs.io/en/stable/](https://ddev.com/get-started/)). Install this software to make use of this development environment.

`ddev start` This command starts the local development environment.

`ddev stop` This command stops the local development environment.

To develop in the application, you can use the `ddev ssh` command. This opens a terminal session in the virtual development environment. Here you can run the `composer install` command to install the dependencies and use other symfony commands.

When everything is correctly set up, you will see this homepage at the URL that appears in your terminal after you have used `ddev start`.


### 1. ddev ssh and run the command composer install
### 2. Run the commands in ddev ssh: composer require symfony/webpack-encore-bundle && npm install bootstrap --save && composer require symfony/http-client
### 3. Populate database with required entities: php bin/console doctrine:fixtures:load
### 4. php bin/console app:fetch-football-data in ddev ssh to fetch eredivise teams ( will be made automatically )
### 5.  php bin/console app:fetch-football-data --standings