#!/bin/sh

cd "$( dirname "${BASH_SOURCE[0]}" )"

if [ "$#" -ne 3 ]; then
  read -p "Adresse du nextdom : " URL
  read -p "Utilisateur : " LOGIN
  read -p "Mot de passe : " PASSWORD
else
  URL=$1
  LOGIN=$2
  PASSWORD=$3
fi

if echo "$URL" | grep -qv "http"
then
  URL="http://$URL"
fi

echo "Test on page $URL"
echo ">>> Connect page <<<"
python3 -W ignore connection_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>> Administration pages <<<"
python3 -W ignore administrations_page.py "$URL" "$LOGIN" "$PASSWORD"
echo ">>> Rescue mode <<<"
python3 -W ignore rescue_page.py "$URL" "$LOGIN" "$PASSWORD"
