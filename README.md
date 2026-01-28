# Restau TyTy - API Back Office

Bienvenue sur le projet backend de Restau TyTy. Ce projet est une API Laravel destinée à gérer les opérations du back-office pour les restaurants, les utilisateurs et les commandes.

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé les outils suivants sur votre machine :

- **PHP** >= 8.2
- **Composer**
- **Node.js** & **NPM**
- **SGBD** (MySQL, MariaDB ou PostgreSQL)

## 🚀 Installation Rapide

Le projet dispose de scripts automatisés pour faciliter l'installation.

1. **Cloner le projet**
   ```bash
   git clone <votre-url-repo>
   cd restau-bo
   ```

2. **Installation automatisée**
   Si vous êtes sous Linux/Mac ou un terminal compatible Git Bash :
   ```bash
   composer run setup
   ```
   *Ce script installe les dépendances PHP et JS, copie le fichier `.env`, génère la clé d'application et lance les migrations.*

   **Ou manuellement :**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

## ⚙️ Configuration

Ouvrez le fichier `.env` et configurez vos accès à la base de données :

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restau_bo
DB_USERNAME=root
DB_PASSWORD=
```

## 🏃‍♂️ Lancement

Pour lancer le serveur de développement ainsi que les workers et Vite (pour les assets frontend si nécessaire), utilisez la commande unifiée :

```bash
composer run dev
```

Cette commande lance en parallèle :
- Le serveur Laravel (`php artisan serve`)
- Le gestionnaire de file d'attente (`php artisan queue:listen`)
- Le serveur de logs (`php artisan pail`)
- Le serveur de build Vite (`npm run dev`)

Sinon, lancez simplement :
```bash
php artisan serve
```
L'API sera accessible sur [http://localhost:8000](http://localhost:8000).

## 📚 Documentation API (Swagger)

La documentation de l'API est générée automatiquement avec Swagger.

- **URL de la doc** : [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
- **Fichier de config** : `config/l5-swagger.php`

Pour regénérer la documentation après une modification des annotations :
```bash
php artisan l5-swagger:generate
```

## ✅ Tests

Pour lancer la suite de tests automatisés :

```bash
php artisan test
```

## 🔑 Rôles et Permissions

Le système utilise `spatie/laravel-permission`. Un seeder est disponible pour initialiser les rôles par défaut :

```bash
php artisan db:seed
```
Role principal : `ADMIN`
