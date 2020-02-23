# NextDom Core Changelog
---
## 0.7

> ***Release Date : 25/02/2020***
>
> ***Statut : beta***
>
> ***Coverall : 23%***

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/28?closed=1)
---
## 0.6.8

> ***Release Date : 03/02/2020***
>
> ***Statut : beta***
>
> ***Coverall : 23%***

#### NEWS
* Nouvelles fonctionnalités d'édition de la programmation des scénarios (copier/couper/coller, déplacement, undo/redo, mémoire....)
* Ajouts de tests [SonarCloud](https://sonarcloud.io/dashboard?id=NextDom_nextdom-core)
* Création de nombreux enums
* Création des méthodes isVisible et isEnabled pour la plupart des classes
* Création des méthodes isType et isSubType pour les commandes et eqLogics
* Création de nouvelles fonctions pour éviter les failles XSS
* Création de méthodes dans LogHelper
* Tranformation de la boîte à message au format adminLTE
* Nouvelle catégorie dans le Market NextDom : Exclusivity
* Nouveau thème de Widget pour le dashboard
* Nouveaux Avatars
* Nouveau security score lors des saisies de mots de passe

#### AMELIORATIONS / MODIFICATIONS
* Merge Jeedom 3.3.41
* Mise à jour, nettoyage et traitement des obsolescence des dépendances
* Amélioration de l'autocomplétion des fonctions scénarios (avec description)
* Réindentation du code
* Remplacement des array('blabla') par ['blabla']
* Nettoyage et amélioration massif de l'ajax
* Amélioration des requetes SQL
* Optimisation du code PHP
* Optimisation du code JS
* Suppression du footer et déplacement des informations dans le sideMenu
* Sécurisation InfluxDB
* Optimisation des chargements CSS
* Restoration du cache d'un backup

#### FIXS
* FIx de la position et du resize des modales
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/25?closed=1)
---
## 0.6.1
> ***Release Date : 10/01/2020***
>
> ***Statut : beta***
>
> ***Coverall : 17%***

#### FIXS
* Fix Plus d'historique depuis 01/01/2020
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/26?closed=1)
---
## 0.6.0

> ***Release Date : 27/11/2019***
>
> ***Statut : beta***
>
> ***Coverall : 17%***

#### NEWS
* Contenu des tuiles du dashboard centré verticalement

#### AMELIORATIONS / MODIFICATIONS
* Merge Jeedom 3.3.38
* Ajout d'un lien direct au testeur d'expression dans le sidemenu
* Tri alphabétique des catégories et nom de plugins dans le sidemenu
* Nettoyage et Amélioration visuelle de la page services
* Revue des pages administration
  * Mise en onglet pour aérer la lecture
  * Onglet iconisés dynamique suivant résolution
* Ajout de tests unitaires
* Amélioration du score sonarCloud

#### FIXS
* Fix de l'interpretation mauvaise des ON/OFF dans les expressions et des commandes binaires
* Fix failles de sécurité
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/19?closed=1)
---
## 0.5.4

> ***Release Date : 13/11/2019***
>
> ***Statut : beta***
>
> ***Coverall : 17%***

#### NEWS
* Possibilité de trier les logs chronologiquement ou alphabetiquement

#### AMELIORATIONS / MODIFICATIONS
* Amélioration du score SonarCloud

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/24?closed=1)
---
## 0.5.3

> ***Release Date : 09/11/2019***
>
> ***Statut : beta***
>
> ***Coverall : 17%***

#### NEWS
* Choix du logo NextDom pour le thème

#### AMELIORATIONS / MODIFICATIONS
* Nettoyage du code inutile
* Suppression page realtime

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/23?closed=1)
---
## 0.5.2

> ***Release Date : 08/11/2019***
>
> ***Statut : beta***
>
> ***Coverall : 17%***

#### NEWS
* Nouvelle gestion des themes
* Fusion des pages Customisation et Profil
* Ajout du choix de l'icone NextDom
* Réintegration de l'aide
* Intégration de SonarCloud pour améliorer la qualité du code
* Authentification 2 factors se fait maintenant en 2 étapes

#### AMELIORATIONS / MODIFICATIONS
* Suppression de la fonctionnalité user.function
* Suppression de la fonctionnalité envoi plugin sur le market
* Amelioration du comportement des modales
* Amélioration de l'intégration de la version mobile
* Amélioration de la couverture des traductions
* Nettoyage du code
* Correction InfluxDb

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/22?closed=1)
---
## 0.5.1

> ***Release Date : 27/10/2019***
>
> ***Statut : beta***
>
> ***Coverall : 14%***

#### NEWS
* Changement du "Loading" par défaut par le "Pace Loading" AdminLTE

#### AMELIORATIONS / MODIFICATIONS
* Suppression de la fonctionnalité Personnalisation JS/Css du Core
* Nouveau tests unitaires du core

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/21?closed=1)
---
## 0.5.0

> ***Release Date : 20/10/2019***
>
> ***Statut : beta***

#### NEWS
* Affichage des scénarios sur le dashboard
* Possibilité de commander les scénarios sur le dashboard
* Déplacement du code du core (/core)
* Mise à jour des dépendances
* Séparation de la version mobile qui devient indépendante, l'installation est proposée au login
* Nouveau tests unitaires du core (coverall > 10%) (La couverture à chutée du fait d'une couverture structurelle globale du core)
* Test unitaires version mobile (coverall > 41%)

#### AMELIORATIONS
* Amélioration de la migration depuis Jeedom
* Amélioration de compatibilité PHP > 7.2 et Debian Buster
* Amélioration du système de validation (ajout des nightly et d'un triple LXC de validation automatique d'install & update)

#### FIXS
* Correction de l'affichage des updates du Core
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/18?closed=1)
---

## 0.4.2
> ***Release Date : 13/09/2019***
>
> ***Statut : beta***

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/19?closed=1)
---
## 0.4.1
> ***Release Date : 12/09/2019***
>
> ***Statut : beta***

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/20?closed=1)
---
## 0.4.0
> ***Release Date : 11/09/2019***
>
> ***Statut : beta***

#### NEWS
* Merge Jeedom 3.3.30
* Sliders : les champs numériques deviennent des sliders au design NextDom
* Statuts et contrôle des scénarios en version mobile
* Nouvelle disposition plus responsive des champs dans les pages

#### AMELIORATIONS
* Ajout des titres des pages sur l'onglet navigateur en version mobile
* Nettoyage massif du code
* Ajouts de commentaires dans les fichier JS
* Suppression de 6 librairies externes au profit d'une intégration adminLTE
* Amélioration des tests

#### CHANGEMENTS
* Suppression de l'édteur de fichier
* Suppression du mode rescue
* Suppression des raccourcis clavier Windows & Mac de sauvegarde

#### FIXS
* Suppression dossier config
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/16?closed=1)
---
## 0.3.1
> ***Release Date : 05/09/2019***
>
> ***Statut : beta***

#### FIXS
* Corrections des liens symbolique dossier tmp ecris en dur par certains plugins
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/17?closed=1)
---
## 0.3.0
> ***Release Date : 25/07/2019***
>
> ***Statut : beta***

#### NEWS
* Nombreux visuels mis en standard AdminLTE
* Nouvelle timeline

#### AMELIORATIONS
* Améliorations graphiques diverses
* Amélioration de la lisibilité du code
* Amélioration des tests

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/12?closed=1)
---
## 0.2.0
> ***Release Date : 29/05/2019***
>
> ***Statut : beta***

#### NEWS
* Barre de raccourcis sur le dashboard
* Tests unitaires et d'intégration : Prise en charge de la couverture du code
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/7?closed=1)

#### AMELIORATIONS
* Amélioration et optimisation de l'interface graphique
    * Affichage des markets
    * Fenêtre modales retravaillées
* Amélioration du système de sauvegarde
* Amélioration de la lisibilité du code source
* Amélioration des performances
* Plus de tests

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/9?closed=1)

---
## 0.1.3
> ***Release Date : 11/03/2019***
>
> ***Statut : beta***

#### NEWS
* Ajout du thème sombre
* Ajout des fonctionnalités du core jeedom
* Ajout de la fonction recherche centralisée

#### AMELIORATIONS
* Améliorations graphiques
* Améliorations des performances
* Amélioration de la lisibilité du code
* Amélioration des tests

#### FIXS
* [liste ici](https://github.com/NextDom/nextdom-core/milestone/6?closed=1)
---
## 0.1.2
> ***Release Date : 04/02/2019***
>
> ***Statut : beta***

#### NEWS
* Ajout de themes (dark, white et mixte)
* Utilisation du router symfony

#### AMELIORATIONS
* Optimisations du core (passage des pages et modals en TWIG)

#### FIXS
* Correction de bugs
---
## 0.1.1
> ***Release Date : 31/01/2018***
>
> ***Statut : beta***

#### FIXS
* Correction de bugs

## 0.1.0
> ***Release Date : 25/01/2019***
>
> ***Statut : beta***

#### NEWS
* Premiere version publique du core NextDom  
---

# Bug

En cas de problèmes, il est possible d'ouvrir un ticket pour demander une correction :

[https://github.com/NextDom/nextdom-core/issues](https://github.com/NextDom/nextdom-core/issues)
