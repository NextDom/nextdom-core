## Installation via docker

### Description

Ce document décrit comment créer un conteneur docker a partir des paquets debian de la team.

### Pre-requis

- docker installé
- un accès internet

### Construction de l'image et lancement des services 

Aucune image docker existe pour le moment, il faut la construire via le script docker_deb.sh. 
Le script va egalement construire l'image et le conteneur et le lancer.

Le .env contient les ports d'accès et les devices à ajouter quand l'option -u est utilisée

Le script docker_deb.sh utilise un conteneur comme une machine virtuelle et n'utilise pas les avantages de docker (optimisation de la place, performance, limitation du nombre de layer, ....).


### Parametres du docker_build.sh

docker_deb.sh -h

./docker_deb.sh: [d,m,(u|p)]
	without option, container is built from nextdom's debian packages and has no access to devices
	d	container is in demo mode, php modules are disabled to limit surface of attack when nextdom is exposed to unknown users/testers.
	p	container has access to all devices (privileged: not recommended)
	u	container has access to ttyUSB0
	h	This help


### Acces au container

Depuis le repertoire install/OS_specific/Docker/debian/

```docker-compose exec nextdom-deb bash```

la bdd mysql est accessible via le port $MYSQLMAP (3326 si non modifié, l'info est dans le .env)

les login et mdp mysql utilisés sans disponible dans le contneur via un 

```cat /usr/share/nextdom/core/config/common.config.php```

##