### Installation du core
Pour installer NextDom, vous aurez besoin d’une distribution Debian (ou Rasbian) 9 fraîchement installée.

Une fois votre installation prête vous n’aurez qu’à exécuter cette commande :
```
wget -O- https://raw.githubusercontent.com/NextDom/NextDom-DebInstaller/master/deb-install.sh | sudo bash
```
Ce script va installer l’ensemble des dépendances nécessaires puis installer NextDom.

Installation manuelle ?
Vous pouvez également l’installer manuellement en exécutant les commandes suivantes :
```
apt install -y software-properties-common gnupg wget
add-apt-repository non-free
wget -qO - http://debian.nextdom.org/debian/nextdom.gpg.key | apt-key add -
echo "deb http://debian.nextdom.org/debian nextdom main" >/etc/apt/sources.list.d/nextdom.list
apt update
apt -y install nextdom
```

Ok et après ?
Une fois installé, vous pourrez accéder à votre NextDom dans un navigateur internet à l’adresse :

```
http://IP_DE_VOTRE_MACHINE
```

Pour rappel, pour connaitre l’IP de votre installation, vous pouvez l’obtenir en tapant :

```
ip addr
```

### Configuration du core

