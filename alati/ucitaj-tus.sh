#!/bin/bash
# Učitavanje jedne datoteke na hosting (Hostinger TUS). Ključeve (U, A, R) daje hosting_files_generate-upload-url;
# spremaju se privremeno u kljuc.env pored skripte i brišu nakon objave. Nikad ih ne spremati u git.
# ucitaj.sh <lokalna datoteka> <putanja u public_html>
. "${KLJUC:-$(dirname "$0")/kljuc.env}"
F="$1"; D="$2"; SIZE=$(stat -c%s "$F")
c1=$(curl -s -o /dev/null -w '%{http_code}' -X POST "$U/$D?override=true" -H "X-Auth: $A" -H "X-Auth-Rest: $R" -H "Tus-Resumable: 1.0.0" -H "Upload-Length: $SIZE" -H "Upload-Offset: 0")
c2=$(curl -s -D - -o /dev/null -X PATCH "$U/$D?override=true" -H "X-Auth: $A" -H "X-Auth-Rest: $R" -H "Tus-Resumable: 1.0.0" -H "Content-Type: application/offset+octet-stream" -H "Upload-Offset: 0" --data-binary "@$F" | tr -d '\r' | awk '/^HTTP\//{c=$2} tolower($1)=="upload-offset:"{o=$2} END{print c" offset="o}')
echo "$D  POST=$c1 PATCH=$c2 size=$SIZE"
[ "$c1" = 201 ] && echo "$c2" | grep -q "^204 offset=$SIZE$"
