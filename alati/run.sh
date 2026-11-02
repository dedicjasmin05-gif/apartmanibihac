#!/bin/sh
# Pomoćna skripta za provjere preko Hostinger crona (mijenja se po potrebi).
P=/home/u227550228/domains/apartmanibihac.com/public_html/wp-content/plugins/apartmani-upiti
D=/home/u227550228/domains/apartmanibihac.com/public_html/wp-content/uploads/abu-tmp
mkdir -p $D
php $P/alati/slike.php 25
for u in / /team-building-bihac/ /rafting-na-uni/ /en/team-building-bihac/; do
  n=$(echo $u | tr -d / ); [ -z "$n" ] && n=naslovnica
  curl -s "https://apartmanibihac.com$u" > $D/$n.html
done
ls -la $D | tail -n +4
