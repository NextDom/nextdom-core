#!/bin/bash

docker exec -i $4 bash <<EOF
sed -i 's#$1#$2#g' $3
EOF
