# NextDom by NextDom Team #

Website (English): [https://www.nextdom.org/en/](https://www.nextdom.org/en/)

Website (French):  [https://www.nextdom.org/](https://www.nextdom.org/)

# Installation #

## Pre-requis
- MySQL installé (en local ou sur une machine distance)
- un serveur web installé (apache ou nginx)
- php (7.0 minimum) installé avec les extensions : curl, json, gd et mysql
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

## Installation via docker

### Pre-requis

- docker installé

### Construction de l'image et lancement des services 

Aucune image docker existe pour le moment, il faut la construire (Dockerfile.develop)
Le script ci dessous va construire l'image et les conteneurs et les lancer.

les alias permettent une accés rapide aux informations

```#!/usr/bin/env bash

DKRFILE=Dockerfile.develop
TAG=nextdom/dev
YML=docker-compose-nextdom.yml

docker build -f ${DKRFILE} . --tag ${TAG}
docker-compose -f ${YML} up -d
source .env
docker attach ${}

```

/!\ particularité du au dépot privé, il faut lancer le init.sh dans le conteneur nextdom-dev pour avoir les invites (login/pwd) git du projet. 

### Parametres du install.sh

options du script:

* -d NOMSERVEURSQL: par défaut localhost, permet de définir le serveur sql. 
* -h : sorite au format html
* -m mysql.root.password: mot de passe de root pour mysql
* -n nextdom.mysql.user.password: mot de passe de l'utilisateur nextdom pour mysql
* -o par defaut /dev/null: si utilisé, redirection dans /tmp/output
* -s [01-21]: reprend l'installation à l'étape demandé et poursuit.
* -v [master/develop]: lors du git clone reprend cette branche
* -w WEBSERVER_HOME: emplacement racine de nextdom

### Acces aux containers

* nextdom-dev (serveur apache/php) est accessible en ssh .
* nextdom-mysql est accessible via mysql sur le port 3326. 