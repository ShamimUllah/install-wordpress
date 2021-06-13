# Requirments

- Ubuntu 20.04 -64-bit
- PHP >= 7.3
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- **PORT 80 should be free**
- Composer

## Installation commands

1. Clone repository:
```
git clone https://github.com/ShamimUllah/install-wordpress.git && cd install-wordpress

```

2. Application setup:
```
cp .env.example .env
composer install
php artisan key:generate
```

3. Folder permission:
```
sudo chmod -R 777 bootstrap/cache
sudo chmod -R 777 storage
```

## Usable commands

1. Install wordpress command:
```
php artisan install:wordpress
```

2. Stop website command
```
php artisan install:wordpress stop-site
```


3. Start website command
```
php artisan install:wordpress start-site
```


4. Delete website command
```
php artisan install:wordpress remove-site
```