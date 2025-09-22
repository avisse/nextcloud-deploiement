#  Projet – Nextcloud : Espace de collaboration & fichiers (Docker)

## Contexte & objectifs
La collectivité souhaite centraliser **stockage, partage et collaboration** (fichiers, agendas, contacts) en environnement **auto‑hébergé** et conforme **RGPD**.
Objectifs du pilote : 30–50 comptes, stack **Docker Compose** simple et reproductible, **persistance** claire (data/config/apps) et **performances** via APCu/Redis.

##  Architecture
- **app** : `nextcloud:stable-apache` (Apache inclus)
- **db** : `mariadb:lts`
- **redis** : `redis:alpine` (file locking & cache)
- **Volumes** :  
  - `./data` → `/var/www/html/data`  
  - `./config` → `/var/www/html/config`  
  - `./custom_apps` → `/var/www/html/custom_apps`  
  - `./db` → `/var/lib/mysql`

##  Déploiement – `docker-compose.yml`
```yaml
version: '3.8'

services:
  db:
    image: mariadb:lts
    container_name: nextcloud_db
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: rootpass
      MYSQL_PASSWORD: nextcloudpass
      MYSQL_DATABASE: nextcloud
      MYSQL_USER: nextcloud
    volumes:
      - ./db:/var/lib/mysql
    networks: [nextcloud]

  redis:
    image: redis:alpine
    container_name: nextcloud_redis
    restart: always
    networks: [nextcloud]

  app:
    image: nextcloud:stable-apache
    container_name: nextcloud_app
    restart: always
    depends_on: [db, redis]
    environment:
      MYSQL_HOST: db
      MYSQL_DATABASE: nextcloud
      MYSQL_USER: nextcloud
      MYSQL_PASSWORD: nextcloudpass
      REDIS_HOST: redis
      NEXTCLOUD_ADMIN_USER: admin
      NEXTCLOUD_ADMIN_PASSWORD: adminpass
      # Garder UNIQUEMENT si un reverse proxy nommé "reverse_proxy" est présent
      TRUSTED_PROXIES: reverse_proxy
      OVERWRITEPROTOCOL: http
    volumes:
      - ./data:/var/www/html/data
      - ./config:/var/www/html/config
      - ./custom_apps:/var/www/html/custom_apps
    ports:
      - "8082:80"
    networks: [nextcloud]

networks:
  nextcloud:
```
> **Important** : monter **/var/www/html/data** (et non tout `/var/www/html`) pour ne pas écraser le core Nextcloud.

## Mise en service
1. **Prérequis** : Debian 12, Docker & Compose installés, DNS/FQDN si besoin.  
2. **Arborescence** :
```
nextcloud-deploiement/
  docker-compose.yml
  config/         # config.php + *.config.php (redis/apcu…)
  custom_apps/
  data/
  db/
  backups/
  README.md
```
3. **Démarrage** :
```bash
docker compose up -d
# Accès: http://IP_VM:8082
# Admin auto (si variables définies) : admin / adminpass (à changer)
```

## Optimisations post‑install
- `config/apcu.config.php`
```php
<?php
$CONFIG['memcache.local'] = '\OC\Memcache\APCu';
```
- `config/redis.config.php`
```php
<?php
$CONFIG['memcache.locking'] = '\OC\Memcache\Redis';
$CONFIG['redis'] = ['host' => 'redis', 'port' => 6379];
```
- Extraits utiles `config/config.php`
```php
<?php
$CONFIG = [
  'trusted_domains' => ['localhost','10.0.0.10','cloud.collectivite.local'],
  'overwrite.cli.url' => 'http://cloud.collectivite.local',
  'default_phone_region' => 'FR',
];
```
- **Cron** (toutes les 5 min) :
```bash
*/5 * * * * docker compose exec -u www-data nextcloud_app php -f cron.php >/dev/null 2>&1
```

## Sécurisation (paliers)
- **Pilote interne** : accès LAN, secrets forts (`rootpass`, `nextcloudpass`, `adminpass`), sauvegardes basiques.
- **Pré‑prod / Prod** : reverse proxy (Traefik/NPM/Caddy) + **TLS Let’s Encrypt**, ajuster `TRUSTED_PROXIES` (IP/CIDR) & `OVERWRITEPROTOCOL=https`, headers sécurité (HSTS/CSP), fail2ban, apps en liste blanche, quotas & 2FA.

## Sauvegardes & restauration
Script simple de sauvegarde (DB + config + data) :
```bash
#!/usr/bin/env bash
set -euo pipefail
STAMP=$(date +%F_%H%M)
mkdir -p backups
mysqldump -u nextcloud -pnextcloudpass -h 127.0.0.1 nextcloud > backups/db_${STAMP}.sql
tar -czf backups/config_${STAMP}.tar.gz config/
tar -czf backups/data_${STAMP}.tar.gz data/
find backups -type f -mtime +14 -delete
echo "Backup OK -> ${STAMP}"
```
Restauration (schéma) : `docker compose down` → restaurer SQL + `config/` + `data/` → `docker compose up -d` → vérifier logs, `occ maintenance:mode --off` au besoin.

## Administration (occ)
```bash
docker compose exec --user www-data nextcloud_app php occ status
docker compose exec --user www-data nextcloud_app php occ app:list
docker compose exec --user www-data nextcloud_app php occ user:add alice
docker compose exec --user www-data nextcloud_app php occ maintenance:mode --on
```

##  Résultats (pilote)
- Plateforme unique de partage & collaboration
- Moins d’e-mails lourds (liens sécurisés)
- Accès web/bureau/mobile
- Base solide pour montée en charge & ouverture sécurisée


