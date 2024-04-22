## Welcome to the application Eredivise Stats Tracker

## To get the project running:

#### 1. git clone https://github.com/netas369/Eredivisie-Stats-Tracker
#### 2. Open terminal and start the development environment by writing command 'ddev start'
#### 3. Open development terminal by writing command 'ddev ssh'
#### 4. In development terminal write command 'composer install'
#### 5. In development terminal write commands 'npm install' && 'npm run dev'
#### 6. In normal terminal session write command 'ddev fetch-football-data'. It will run all the required commands for fetching data and populating the database with required entitities.


## Application Explained

### '/' Home Page
#### If the user is not logged in the user will be able just to see the details of every team of Eredivise League. The User can see all games of the season upcoming and already played ones. 
#### If the user is logged in it can also follow teams and see the followed teams at the top of the page, where user can quickly find his favorite teams, view details about the teams or unfollow the team.

### '/standings' Standings Page
#### Page that can be accessed both by registered users and non registered users to see the Eredivise League Standings

### '/followed' My Teams Page
#### My teams page is accesible only by registered users where the user can quickly see last match and upcoming matches of his followed teams.

### '/login' Login Page
#### Login page to authenticate registered user

### '/register' Register Page
#### Register page used to register new user

#### '/logout' Log Out URL
#### Log Out URL can be accesed by registered users, it can be found on navigation bar named Logout.

## Bugs in the system to be fixed
#### '/logout' URL indeed logsout user, but on redirection it throws the error. Reason yet unknown might be because local development server is not https secured.


### Made by Netas Neverauskas
