## Open lararvel project in visual studio or some other IDE and copy .env.example to .env. You can open docker-compose.yml and .env to customize it if u need   
```
$ cd path/to/FavouriteCurrencyProject
$ docker-compose --env-file ./laravel_project/laravel/.env up -d
$ docker exec -it nginx_favourite_currency bash
$ cd app/laravel/
$ composer install
```
## This is a little unpleasent part. we need to setup crontab entry So our laravel will make call to NBP API every minute.
$ crontab -e 
## click "ESC", click "i", Write below comments "* * * * * cd /app/laravel && /usr/local/bin/php artisan schedule:run >> cron_log.txt", Click "ESC" Write ":wq"
```
$ php artisan migrate
$ php artisan l5-swagger:generate
```
## Now You can acces Swagger documentation. Copy Paste it to the browser "http://127.0.0.1:8080/api/documentation" enjoy!
## to genreate JWT token for accesing protected routes just register new user and copy WHOLE string (for example: "Bearer 3|RA5dpgiJpLlac17_REST_OF_THE_TOKEN") with acces token and paste it to the "padlock" icon.
## to look inside database You can access pgadmin in the browser "http://localhost:5559"
##	pgadmin login	
##		login = pgadmin_email@pgadmin.org
##		password = secretadmin
##	Rigth Click On Serwer->register->serwer->connection
##		hotstname = postgres_favourite_currency
##		port = 5432
##		database = postgres
##		user = postgres
##		password = secret
## if for some reason CRON is not working correctly with Laravel You can fetch data from NPB API by using this artisan command:    
```
$ php artisan NBP:currency
```
