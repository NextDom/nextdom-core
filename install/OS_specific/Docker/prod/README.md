## Installation via docker

### Pre-requis

- docker installé
- .env, envMysql, envProd, githubtoken.txt: renseigner les informations ports, mdp bdd, user, token github

### Construction de l'image et lancement des services 

Aucune image docker existe pour le moment, il faut la construire via le script docker_prod.sh. 
Le script va egalement construire l'image et les conteneurs et les lancer.

la configuration requise à la construction des conteneurs est dans .env, dans envWeb on a les variables accessibles au conteneurs apache
la configuration envMysql pour le conteneur mysql.

les infos sensibles sont données en ARG de build ( mdp bdd ) et ne restent pas disponibles dans le conteneur à l'éxécution.

/!\ particularité du au dépot privé, il faut creer le fichier githubtoken.txt
qui contient un token ou le login:mdp ayant accès au dépot nextdom-core.

Pour mysql, dans le fichier envMysql, il faut changer les mots de passe root et user
 * MYSQL_ROOT_PASSWORD=changeItTwo
 * MYSQL_PASSWORD=changeIt)

Pour le web, dans le fichier .env, il faut changer le mot de passe root
* ROOT_PASSWORD=changeIt

Cette variable n'est utile que dans le cas d'utilisation du serveur SSH si il est installé

Le script docker_prod.sh est adapté pour la production

Le code html/css/js est dans le volume wwwdata-prod, les données mysq sont dans le volume mysqldata-prod

### Parametres du docker_build.sh

options du script:

*	sans option, aucun acces aux périphériques.
*	TODO p	le conteneur a accès à touts les périphériques (privileged: non recommandé)
*	TODO u	le conteneur a accès au périphérique ttyUSB0
*	TODO m	le conteneur est en mode démo ou dev (disponible uniquement avec les paquets debian)
*   z   le conteneur sera complété par le projet local au lieu d'un git clone.
*   k   les volumes ( web et mysql) sont conservés ainsi que leurs contenus.
*	h	This help

### outils containers

* Verification de la configuration de la bdd

``docker-compose run --rm nextdom-web cat /var/www/html/core/config/common.config.php``

* Verification des users et hosts

```docker-compose run --rm nextdom-mysql /usr/bin/mysql -uroot -hlocalhost -pMnextdom96 -e 'select user,host from mysql.user;'```

#### Quand le système ne s'éxécute pas 
* Accès au conteneur web 

``` docker-compose run --rm nextdom-web bash```
* Accès au conteneur mysql

```docker-compose run --rm nextdom-mysql bash```

#### Quand le système s'éxécute 
* Accès au conteneur web 

```docker-compose exec nextdom-web bash```
* Accès au conteneur mysql

```docker-compose exec nextdom-mysql bash```

* Copie d'un backup dans le conteneur web depuis le repertoire install/OS_specific/Docker/prod/

```
docker cp ../../../../backup/backup-myNextom.mydomain.tld-date.time.tar.gz $(docker-compose ps -q nextdom-web):/var/www/html/backup/
```