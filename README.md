# NextDom by Loïc #

Website (English): [https://nextdom.com/site/en/](https://nextdom.com/site/en/)

Website (French):  [https://nextdom.com/site/](https://nextdom.com/site/)

# Installation #

## Pre-requis
- MySQL installé (en local ou sur une machine distance)
- un serveur web installé (apache ou nginx)
- php (5.6 minimum) installé avec les extensions : curl, json, gd et mysql
- ntp et crontab installés
- curl, unzip et sudo installés

TIPS : pour nginx vous trouverez un exemple de la configuration web nécessaire dans install/nginx_default.

### Création de la BDD nextdom

Il vous faut créer une base de données nextdom sur MySQL (en utf8_general_ci).

### Téléchargement des fichiers

Téléchargez les sources nextdom : https://github.com/nextdom/core/archive/stable.zip, décompressez-les dans un répertoire de votre serveur web.

### Configuration et installation

Allez (avec votre navigateur) sur `install/setup.php`.

Remplissez les informations, validez et attendez la fin de l'installation. Les identifiants par défaut sont admin/admin.
