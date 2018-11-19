## Installation via docker

### Description

Ce document décrit comment créer un conteneur docker a partir des paquets debian de la team.

### Pre-requis

- docker installé
- un accès internet

### Construction de l'image et lancement des services 

Aucune image docker existe pour le moment, il faut la construire via le script docker_build.sh. 
Le script va egalement construire l'image et le conteneur et le lancer.

le .env contient les ports d'accès

le script docker_deb.sh utilise les conteneurs comme des machines virtuelles et n'utilise pas les avantages de docker.


### Parametres du docker_build.sh

pad d'options 

### Acces au container

Depuis le repertoire install/OS_specific/Docker/ 

```docker-compose -f docker-compose-deb.yml exec nextdom-deb bash```

la bdd mysql est accessible via le port $MYSQLMAP (3326 si non modifié, l'info est dans le .env)

les login et mdp mysql utilisés sans disponible dans le contneur via un 

```cat /usr/share/nextdom/core/config/common.config.php```

##