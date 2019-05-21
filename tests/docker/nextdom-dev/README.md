# docker-nextdom-core-dev

Image permettant le développement de Nextdom

# Utilisation

Lancer le container:
```bash
sudo docker run -d --name=nextdom --privileged=true -p 8080:80 -v $(pwd)/nextdom-core/:/usr/share/nextdom  nextdom/nextdom-core-dev
```

# Fonctionnalités optionnelles

## Server samba

Installe et configure un server samba pour les testes de sauvegarde.
* utilisateur: nextdom
* mot de passe: nextdom
* ip: localhost
* chemin sur le système de fichier: /var/backups

Pour construire l'image docker avec cette fonctionnalité:
```
cd ./tests/docker/nextdom-dev/
docker build --build-arg MODE=dev --build-arg ENABLE_SMB=1 -t nextdom-core:dev .
```


# F.A.Q

## Le mysql du container ne démarre pas

Si vous constatez que le myql du container ne démarre pas, et que vous trouvez
la ligne suivante dans les logs de `journalctl -xe`:
```
apparmor="DENIED" operation="open" info="Failed name lookup - disconnected path" error=-13 profile="/usr/sbin/mysqld" name="var/lib
```

Alors ajouter un profile `apparmor` dans votre hôte (pas dans le container)

```bash
cat - | sudo tee /etc/apparmor.d/explicitly-unconfined <<EOS
profile explicitly-unconfined flags=(attach_disconnected,mediate_deleted) {
  capability,
  change_profile -> **,
  dbus,
  file,
  mount,
  network,
  pivot_root,
  ptrace,
  signal,
  umount,
  unix,
}
EOS
```

Rechargez la configuration `apparmor`:
```bash
sudo service apparmor reload
```

Et lancez votre container comme suit:
```bash
sudo docker run -d --name=nextdom --privileged=true --security-opt apparmor=explicitly-unconfined -p 8080:80 -v $(pwd)/nextdom-core/:/usr/share/nextdom  nextdom/nextdom-core-dev
```

<!-- Local Variables: -->
<!-- ispell-local-dictionary: "francais" -->
<!-- End: -->
