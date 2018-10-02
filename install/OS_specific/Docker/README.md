## Installation via docker

### Pre-requis

- docker installé

### Construction de l'image et lancement des services 

Aucune image docker existe pour le moment, il faut la construire via le script docker_build.sh. 
Le script va egalement construire l'image et les conteneurs et les lancer.

toute la configguration est dans le fichier .env

les infos sensibles sont données en ARG de build ( mdp bdd, token github ) et ne restent pas disponibles dans le conteneur à l'éxécution.


/!\ particularité du au dépot privé, il faut lancer le init.sh dans le conteneur nextdom-dev pour avoir les invites (login/pwd) git du projet. 

### Parametres du docker_build.sh

options du script:

*	sans option, aucun acces aux périphériques.
*	p	le conteneur a accès à touts les périphériques (privileged: non recommandé)
*	u	le conteneur a accès au périphérique ttyUSB0
*	m	le conteneur est en mode démo ou dev (disponible uniquement avec les paquets debian)
*	h	This help

### Acces aux containers

* nextdom-dev (serveur apache/php) est accessible en ssh .
* nextdom-mysql est accessible via mysql sur le port 3326.