#!/bin/sh
# Pomoćna skripta za provjere preko Hostinger crona (mijenja se po potrebi).
D=/home/u227550228/domains/apartmanibihac.com/public_html/wp-content/uploads/abu-tmp
mkdir -p $D
for u in / /restorani-bihac/ /team-building-bihac/ /nocni-zivot-bihac/ /vodic/; do
  n=$(echo $u | tr -d / ); [ -z "$n" ] && n=naslovnica
  curl -s "https://apartmanibihac.com$u" > $D/$n.html
done
ls -la $D
