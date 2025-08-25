# Todo List App - Setup Instructions

## Clone the Repository
- copy this repository: https://github.com/JakePatolilic/todo-list-app.git
- use clone git repository and paste the link.

## Install Dependencies
- inside the terminal, run composer install
- run npm install
- run cp .env.example .env to create a .env file

## Setups
- make sure apache and mysql is running
- copy the content of .env.example and paste it in .env
- run php artisan key:generate
- run php artisan migrate
- run npm run build
- open a new terminal and run php artisan serve
- open the link provided