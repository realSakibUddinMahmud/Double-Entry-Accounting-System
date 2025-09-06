## Environment Setup (MySQL) and First Run

This guide documents the exact steps, commands, and quick smoke verification used to run the Laravel multi-tenant accounting app locally with MySQL.

### Prerequisites

- Linux host with sudo
- MySQL 8, PHP 8.3+ (we used PHP 8.4), Composer
- Node (optional for front-end dev)

### 1) Clone/Extract

- Repository path: `/workspace/Double-Entry-Accounting-System`

### 2) Configure .env (MySQL landlord/tenant)

Commands:

```
cd /workspace/Double-Entry-Accounting-System
cp -n .env.example .env
sed -i 's/^APP_ENV=.*/APP_ENV=local/' .env
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/^#\?\s*DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/^#\?\s*DB_PORT=.*/DB_PORT=3306/' .env
sed -i 's/^#\?\s*DB_DATABASE=.*/DB_DATABASE=landlord_master/' .env
sed -i 's/^#\?\s*DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/^#\?\s*DB_PASSWORD=.*/DB_PASSWORD=secret/' .env
grep -q '^DATABASE_URL_TENANT=' .env && sed -i 's|^DATABASE_URL_TENANT=.*|DATABASE_URL_TENANT="mysql://root:secret@127.0.0.1:3306/tenant_demo?charset=utf8mb4"|' .env || echo 'DATABASE_URL_TENANT="mysql://root:secret@127.0.0.1:3306/tenant_demo?charset=utf8mb4"' >> .env
```

### 3) Dependencies

Install Composer locally and vendor packages:

```
cd /workspace/Double-Entry-Accounting-System
php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
php composer-setup.php --install-dir=. --filename=composer.phar
php composer.phar install --no-interaction --prefer-dist --no-progress
```

Create cache/storage directories and app key:

```
mkdir -p bootstrap/cache
mkdir -p storage/framework/{cache,sessions,views,testing} storage/logs
chmod -R 775 storage bootstrap/cache
php artisan key:generate --force
```

### 4) MySQL 8 setup

Start MySQL (non-systemd example) and set root password:

```
sudo mkdir -p /run/mysqld && sudo chown -R mysql:mysql /run/mysqld
sudo /usr/sbin/mysqld --daemonize --user=mysql --skip-networking=0 --socket=/run/mysqld/mysqld.sock
sleep 5
# If needed: set root password via socket or TCP
mysql -h 127.0.0.1 -uroot -e "SELECT VERSION();"
```

Create landlord and tenant databases:

```
mysql -h 127.0.0.1 -uroot -psecret -e "CREATE DATABASE IF NOT EXISTS landlord_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h 127.0.0.1 -uroot -psecret -e "CREATE DATABASE IF NOT EXISTS tenant_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5) Migrations and tenant row

```
cd /workspace/Double-Entry-Accounting-System
php artisan migrate --database=landlord --path=database/migrations/landlord --force
php artisan migrate --database=tenant --path=database/migrations/tenant --force

# Insert landlord tenant (quotes for reserved column `database`)
mysql -h 127.0.0.1 -uroot -psecret landlord_master -e $'INSERT INTO tenants (name, domain, \x60database\x60, created_at, updated_at) VALUES (\'Acme Inc\',\'localhost\',\'tenant_demo\', NOW(), NOW()) ON DUPLICATE KEY UPDATE \x60database\x60=\'tenant_demo\', updated_at=NOW();'
```

### 6) Seed DB views (tenant)

As defined in `database/seeders/ViewSeeder.php`, we applied them directly:

```
mysql -h 127.0.0.1 -uroot -psecret tenant_demo -e "
CREATE OR REPLACE VIEW product_store_stock_view AS
SELECT
  ps.store_id,
  s.name AS store_name,
  ps.product_id,
  p.name AS product_name,
  ps.base_unit_id,
  bu.name AS base_unit_name,
  ROUND((
    IFNULL((
      SELECT SUM(pi.quantity * u.conversion_factor)
      FROM purchase_items pi
      JOIN purchases pu ON pi.purchase_id = pu.id
      JOIN units u ON pi.unit_id = u.id
      WHERE pi.product_id = ps.product_id
        AND pu.store_id = ps.store_id
    ), 0)
    + IFNULL((
      SELECT SUM(psa.quantity)
      FROM product_stock_adjustments psa
      JOIN stock_adjustments sa ON psa.stock_adjustment_id = sa.id
      WHERE psa.product_id = ps.product_id
        AND sa.store_id = ps.store_id
        AND psa.action = '+'
    ), 0)
    - IFNULL((
      SELECT SUM(si.quantity * u.conversion_factor)
      FROM sale_items si
      JOIN sales sa ON si.sale_id = sa.id
      JOIN units u ON si.unit_id = u.id
      WHERE si.product_id = ps.product_id
        AND sa.store_id = ps.store_id
    ), 0)
    - IFNULL((
      SELECT SUM(psa.quantity)
      FROM product_stock_adjustments psa
      JOIN stock_adjustments sa ON psa.stock_adjustment_id = sa.id
      WHERE psa.product_id = ps.product_id
        AND sa.store_id = ps.store_id
        AND psa.action = '-'
    ), 0)
  ), 2) AS current_stock_qty
FROM
  product_store ps
  JOIN products p ON ps.product_id = p.id
  JOIN stores s ON ps.store_id = s.id
  JOIN units bu ON ps.base_unit_id = bu.id;
"

mysql -h 127.0.0.1 -uroot -psecret tenant_demo -e "
CREATE OR REPLACE VIEW store_product_current_cogs_avg AS
SELECT 
  pi.product_id,
  p.store_id,
  ROUND(SUM(pi.quantity * pi.per_unit_cogs) / NULLIF(SUM(pi.quantity), 0), 2) AS cogs_avg
FROM purchase_items pi
JOIN purchases p ON pi.purchase_id = p.id
WHERE p.status = 1
GROUP BY pi.product_id, p.store_id;
"

# Verify
mysql -h 127.0.0.1 -uroot -psecret tenant_demo -e "SHOW FULL TABLES WHERE Table_type='VIEW';"
```

### 7) Serve and smoke check

```
php artisan serve --host=0.0.0.0 --port=8000 &
sleep 2
curl -I http://127.0.0.1:8000/
```

Expected: HTTP 302 to `/login`, with session cookies set.

### Smoke Checklist (results)

- Login route: 302 → /login (OK)
- Tenant row in landlord: present for `domain=localhost`, `database=tenant_demo` (OK)
- Tenant views created: `product_store_stock_view`, `store_product_current_cogs_avg` (OK)

Next manual steps (recommended):
- Create minimal master data (store, brand, category, unit, product, supplier, customer)
- Post one purchase and one sale; confirm stock changes and basic reports render

