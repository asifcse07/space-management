SHELL := /bin/bash

up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f --tail=200 app

sh:
	docker exec -it space-app bash

app:
	mkdir -p src && docker compose up -d app && \
	docker exec -it space-app bash -lc "composer create-project laravel/laravel:^11.0 ."

install:
	docker exec -it space-app bash -lc "composer install && php artisan key:generate"

migrate:
	docker exec -it space-app bash -lc "php artisan migrate --seed"

queue:
	docker exec -it space-app bash -lc "php artisan queue:work --tries=3"

vite:
	docker exec -it space-node sh -lc "npm run dev"
