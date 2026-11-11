#!/bin/sh
# Provjere prije/poslije (samo čitanje). Pokretanje: sh provjera.sh
B=https://apartmanibihac.com
g() { curl -s -A "Mozilla/5.0 provjera" "$B$1"; }
echo "1 pretraga /smjestaj/?mjesto=Lohovo: $(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$B/smjestaj/?mjesto=Lohovo") kartica=$(g '/smjestaj/?mjesto=Lohovo' | grep -c 'class="card"')"
echo "2 /oprema/bazen/: $(curl -s -o /dev/null -w '%{http_code}' $B/oprema/bazen/) robots=$(g /oprema/bazen/ | grep -o "name='robots' content='[^']*'")"
echo "3 oprema u sitemapu: $(g /wp-sitemap.xml | grep -c oprema)"
echo "4 meta opisa naslovnica/objekt: $(g / | grep -c '<meta name="description"') / $(g /smjestaj/apartman-centar/ | grep -c '<meta name="description"')"
echo "5 hreflang /en/faq/una-river/: $(g /en/faq/una-river/ | sed -n '1,/<\/head>/p' | grep -c 'hreflang=')"
echo "6 h1 /smjestaj-lohovo/: $(g /smjestaj-lohovo/ | grep -c '<h1')  clanak: $(g /2026/10/21/odmor-za-dvoje-bihac/ | grep -c '<h1')"
echo "7 title objekta: $(g /smjestaj/apartman-centar/ | grep -o '<title>[^<]*')"
echo "8 llms ?p=: $(g /llms.txt | grep -c '?p=')"
echo "9 /en/ kartice: $(g /en/ | grep -oE 'card__price">[^<]*' | sort | uniq -c | head -2 | tr '\n' ' ')"
echo "10 BlogPosting na clanku: $(g /2026/10/21/odmor-za-dvoje-bihac/ | grep -c '"BlogPosting"')"
echo "11 og:title naslovnice: $(g / | grep -o 'og:title" content="[^"]*')"
echo "12 povezani objekti na /cesta-pitanja/rafting-pitanja/: $(g /cesta-pitanja/rafting-pitanja/ | grep -c 'abu-povezano')"
echo "13 title clanka: $(g /2026/10/21/rafting-na-uni-za-pocetnike/ | grep -o '<title>[^<]*')"
echo "14 /o-nama/: $(g /o-nama/ | grep -o '<title>[^<]*') optika=$(g /o-nama/ | grep -c Optika)"
echo "15 'popust' uz avans na /cesta-pitanja/rezervacija-i-placanje/: $(g /cesta-pitanja/rezervacija-i-placanje/ | grep -oiE 'avans[^.<]{0,60}popust' | wc -l)"
echo "16 ljubimci na objektu: $(g /smjestaj/vila-enio/ | grep -c 'psi i mačke na upit')"
echo "17 mjerenje skripta: $(g / | grep -c 'abu_mj')"
echo "18 clanak 2024 robots: $(g /2024/09/04/naslov-pronadite-odlican-i-jeftin-smjestaj-u-bihacu-vodic-za-putnike/ | grep -o "name='robots' content='[^']*'")"
echo "19 forma upita: $(g /smjestaj/apartman-centar/ | grep -oE '<form[^>]*abu-form[^>]*>' | grep -oE 'action="[^"]*"')"
echo "20 robots.txt AI disallow: $(g /robots.txt | grep -c 'Disallow: /wp-admin/')"
