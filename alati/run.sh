#!/bin/sh
# Pomoćna skripta za provjere preko Hostinger crona (mijenja se po potrebi).
echo "== stari sajt: logo u medijima"
curl -sk --resolve www.apartmanibihac.com:443:95.217.229.38 "https://www.apartmanibihac.com/wp-json/wp/v2/media?search=logo&per_page=30&_fields=id,source_url,media_details.width,media_details.height" | head -c 4000
echo
curl -sk --resolve www.apartmanibihac.com:443:95.217.229.38 "https://www.apartmanibihac.com/wp-json/wp/v2/media?search=vikendice&per_page=30&_fields=id,source_url" | head -c 3000
echo
curl -sk --resolve www.apartmanibihac.com:443:95.217.229.38 "https://www.apartmanibihac.com/wp-json" | head -c 600
echo
echo "== naslovnica html"
curl -s https://apartmanibihac.com/ | wc -c
