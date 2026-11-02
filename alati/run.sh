#!/bin/sh
P=/home/u227550228/domains/apartmanibihac.com/public_html/wp-content/plugins/apartmani-upiti
php $P/alati/slike.php 25
php $P/alati/provjera.php | tail -4
for u in / /restorani-bihac/ /izleti-bihac/ /team-building-bihac/ /en/day-trips-bihac/ /2026/10/21/kako-rezervirati-smjestaj-bihac/; do echo "$u $(curl -s -o /dev/null -w '%{http_code}' https://apartmanibihac.com$u)"; done
