# Pintasan perintah yang sering dipakai. Semuanya berjalan di dalam Docker,
# jadi tidak perlu memasang PHP/Composer/Node di komputer.

.DEFAULT_GOAL := help
.PHONY: help setup up down restart logs shell migrate fresh admin build test qr telegram-test purge

help: ## Tampilkan daftar perintah
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

setup: ## Pasang pertama kali (build image, install dependensi, migrasi, build aset)
	docker compose build
	docker compose run --rm --no-deps app composer install
	docker compose up -d
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --force
	docker compose run --rm node npm install
	docker compose run --rm node npm run build
	@echo ""
	@echo "Selesai. Buat akun admin dengan: make admin"

up: ## Jalankan semua container
	docker compose up -d

down: ## Hentikan semua container
	docker compose down

restart: ## Restart aplikasi, queue, dan scheduler
	docker compose restart app queue scheduler

logs: ## Ikuti log aplikasi & queue
	docker compose logs -f app queue

shell: ## Masuk ke shell container aplikasi
	docker compose exec app bash

migrate: ## Jalankan migrasi
	docker compose exec app php artisan migrate --force

fresh: ## Kosongkan database dan migrasi ulang (HATI-HATI: semua data hilang)
	docker compose exec app php artisan migrate:fresh --force

admin: ## Buat akun admin baru untuk dashboard
	docker compose exec app php artisan make:filament-user

build: ## Build ulang aset frontend (CSS/JS)
	docker compose run --rm node npm run build

test: ## Jalankan seluruh test
	docker compose run --rm app php artisan test

qr: ## Buat QR code untuk ditempel di gerbang
	docker compose exec app php artisan guestbook:qr

telegram-test: ## Kirim pesan percobaan ke grup Telegram
	docker compose exec app php artisan telegram:test

purge: ## Hapus foto yang sudah lewat masa retensi
	docker compose exec app php artisan guestbook:purge-photos
