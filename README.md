# Cinema Management System

Une application web de gestion de cinéma développée en PHP 8.3+ avec une architecture MVC, permettant de gérer les films, les salles et les séances de manière intuitive et sécurisée.

## 🎬 Fonctionnalités

### Gestion des Films
- ✅ CRUD complet (Créer, Lire, Mettre à jour, Supprimer)
- ✅ Liste paginée avec recherche
- ✅ Filtres par genre et année de sortie
- ✅ Protection contre la suppression de films avec des séances programmées
- ✅ Gestion des affiches

### Gestion des Salles
- ✅ CRUD complet
- ✅ Gestion des capacités et types (Standard, 3D, IMAX, VIP)
- ✅ Vérification des conflits de nom
- ✅ Protection contre la suppression de salles avec des séances futures

### Gestion des Séances
- ✅ CRUD complet
- ✅ Vérification automatique des conflits d'horaire
- ✅ Respect de la durée des films
- ✅ Gestion des prix
- ✅ Interface de planning par salle

### Planning
- ✅ Vue hebdomadaire des séances
- ✅ Organisation par salle
- ✅ Filtres par période
- ✅ Affichage des horaires de début et de fin

## 🛠️ Stack Technique

### Backend
- **PHP 8.3+** (sans framework)
- **MySQL 8.0+** avec PDO
- **Architecture MVC** (Modèle-Vue-Contrôleur)
- **API REST** pour la communication frontend/backend
- **Programmation Orientée Objet**

### Frontend
- **HTML5** sémantique
- **CSS3** avec variables CSS et responsive design
- **JavaScript Vanilla** (ES6+)
- **Fetch API** pour les appels AJAX
- **Font Awesome** pour les icônes

### Sécurité
- **Requêtes préparées** PDO (protection SQL injection)
- **Validation des données** côté serveur et client
- **Protection XSS** et CSRF
- **Headers de sécurité** HTTP
- **Sanitization** des entrées utilisateur

## 📁 Structure du Projet

```
my-cinema/
├── backend/
│   ├── api/                    # Points d'entrée API
│   ├── config/
│   │   ├── database.php       # Configuration base de données
│   │   └── config.php         # Configuration générale
│   ├── controllers/
│   │   ├── FilmController.php # Contrôleur des films
│   │   ├── SalleController.php # Contrôleur des salles
│   │   └── SeanceController.php # Contrôleur des séances
│   ├── models/
│   │   ├── Film.php          # Modèle Film
│   │   ├── Salle.php         # Modèle Salle
│   │   └── Seance.php        # Modèle Séance
│   ├── utils/
│   │   ├── Security.php      # Utilitaires de sécurité
│   │   └── Validator.php     # Validation des données
│   ├── .htaccess             # Configuration Apache
│   └── index.php             # Routeur principal
├── frontend/
│   ├── css/
│   │   └── style.css         # Styles principaux
│   ├── js/
│   │   ├── api.js            # Service API
│   │   ├── app.js            # Application principale
│   │   ├── films.js          # Gestion des films
│   │   ├── salles.js         # Gestion des salles
│   │   ├── seances.js        # Gestion des séances
│   │   └── planning.js       # Gestion du planning
│   ├── images/               # Images statiques
│   └── index.html            # Page principale
├── script.sql                # Script de création de la base
└── README.md                 # Documentation
```

## 🚀 Installation

### Prérequis

- **PHP 8.3+** avec extensions :
  - `pdo_mysql`
  - `json`
  - `mbstring`
  - `fileinfo`
- **MySQL 8.0+** ou **MariaDB 10.5+**
- **Serveur web** (Apache avec mod_rewrite ou Nginx)
- **Composer** (optionnel, pour la gestion des dépendances futures)

### 1. Cloner le projet

```bash
git clone <repository-url>
cd my-cinema
```

### 2. Configurer la base de données

1. Créer une base de données MySQL :
```sql
CREATE DATABASE cinema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importer le script SQL :
```bash
mysql -u votre_utilisateur -p cinema < script.sql
```

3. Configurer la connexion dans `backend/config/database.php` :
```php
private $host = 'localhost';
private $dbname = 'cinema';
private $username = 'votre_utilisateur';
private $password = 'votre_mot_de_passe';
```

### 3. Configurer le serveur web

#### Apache

1. Activer `mod_rewrite` :
```bash
sudo a2enmod rewrite
```

2. Configurer le VirtualHost :
```apache
<VirtualHost *:80>
    ServerName cinema.local
    DocumentRoot /chemin/vers/my-cinema
    
    <Directory /chemin/vers/my-cinema>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Ajouter au fichier `/etc/hosts` :
```
127.0.0.1 cinema.local
```

#### Nginx

```nginx
server {
    listen 80;
    server_name cinema.local;
    root /chemin/vers/my-cinema;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /backend/index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. Vérifier les permissions

```bash
chmod -R 755 .
chmod -R 777 backend/logs  # Pour les logs
```

### 5. Lancer l'application

Ouvrez votre navigateur et accédez à `http://cinema.local`

## 📖 Utilisation

### Navigation

L'application est organisée en 4 sections principales :

1. **Films** : Gestion du catalogue de films
2. **Salles** : Gestion des salles de projection
3. **Séances** : Planification des séances
4. **Planning** : Vue d'ensemble des programmations

### Opérations de base

#### Ajouter un film
1. Aller dans la section "Films"
2. Cliquer sur "Ajouter un film"
3. Remplir le formulaire (titre, durée, genre, année)
4. Cliquer sur "Enregistrer"

#### Créer une séance
1. Aller dans la section "Séances"
2. Cliquer sur "Ajouter une séance"
3. Sélectionner un film, une salle et l'heure
4. Le système vérifie automatiquement les conflits
5. Définir le prix et enregistrer

#### Consulter le planning
1. Aller dans la section "Planning"
2. Sélectionner la période souhaitée
3. Visualiser les séances organisées par salle

## 🔧 API Endpoints

### Films
- `GET /api/films` - Lister les films (avec pagination et filtres)
- `GET /api/films/{id}` - Détails d'un film
- `POST /api/films` - Créer un film
- `PUT /api/films/{id}` - Mettre à jour un film
- `DELETE /api/films/{id}` - Supprimer un film
- `GET /api/films/genres` - Lister les genres
- `GET /api/films/annees` - Lister les années

### Salles
- `GET /api/salles` - Lister les salles
- `GET /api/salles/{id}` - Détails d'une salle
- `POST /api/salles` - Créer une salle
- `PUT /api/salles/{id}` - Mettre à jour une salle
- `DELETE /api/salles/{id}` - Supprimer une salle
- `GET /api/salles/select` - Salles pour sélecteurs
- `GET /api/salles/types` - Types de salles
- `POST /api/salles/check-disponibilite` - Vérifier disponibilité

### Séances
- `GET /api/seances` - Lister les séances
- `GET /api/seances/{id}` - Détails d'une séance
- `POST /api/seances` - Créer une séance
- `PUT /api/seances/{id}` - Mettre à jour une séance
- `DELETE /api/seances/{id}` - Supprimer une séance
- `GET /api/seances/date/{date}` - Séances par date
- `GET /api/seances/planning` - Planning des séances
- `GET /api/seances/upcoming` - Séances à venir

## 🔒 Sécurité

### Mesures implémentées

- **Protection SQL Injection** : Utilisation systématique de PDO avec requêtes préparées
- **Protection XSS** : Échappement des données en sortie et validation en entrée
- **Validation des données** : Contrôle strict des formats et longueurs
- **Headers HTTP sécurisés** : CSP, X-Frame-Options, etc.
- **Gestion des erreurs** : Pas d'affichage d'informations sensibles en production
- **Rate limiting** : Protection contre les attaques par force brute

### Bonnes pratiques

- Toujours valider les entrées côté serveur
- Utiliser les requêtes préparées PDO
- Échapper les données affichées
- Limiter les tailles des uploads
- Journaliser les événements de sécurité

## 🐛 Dépannage

### Problèmes courants

#### Erreur 500 - Internal Server Error
1. Vérifier les logs d'erreurs PHP
2. Confirmer la connexion à la base de données
3. Vérifier les permissions des fichiers

#### Problèmes de routing
1. S'assurer que `mod_rewrite` est activé (Apache)
2. Vérifier la configuration du `.htaccess`
3. Confirmer la configuration du VirtualHost

#### Connexion base de données
1. Vérifier les identifiants dans `database.php`
2. Confirmer que la base de données existe
3. Vérifier que l'utilisateur a les droits nécessaires

### Logs

- **Logs d'application** : `backend/logs/app.log`
- **Logs de sécurité** : `backend/logs/security.log`
- **Logs PHP** : Configurés dans `php.ini`

## 🤝 Contribution

### Guidelines

1. Respecter la PSR-12 pour le style de code
2. Ajouter des commentaires pour les fonctions complexes
3. Tester toutes les modifications
4. Mettre à jour la documentation

### Processus

1. Forker le projet
2. Créer une branche feature
3. Implémenter les modifications
4. Tester
5. Soumettre une pull request

## 📝 License

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Pour toute question ou problème :

- Créer une issue sur GitHub
- Consulter la documentation
- Vérifier les logs d'erreurs

---

**Développé avec ❤️ en PHP 8.3+**
