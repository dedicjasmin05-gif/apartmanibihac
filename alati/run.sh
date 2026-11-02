#!/bin/sh
D=/home/u227550228/domains/apartmanibihac.com/public_html/wp-content/uploads/abu-tmp
for u in / /team-building-bihac/ /restorani-bihac/ /2026/11/02/team-building-na-uni-zasto-firme-biraju-bihac/; do
  n=$(echo $u | tr -d / ); [ -z "$n" ] && n=naslovnica
  curl -s "https://apartmanibihac.com$u" > $D/$n.html
done
ls $D
