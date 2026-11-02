#!/bin/sh
curl -s "https://apartmanibihac.com/izleti-bihac/?x=$(date +%s)" | grep -o '[a-z]*\.css[^ ]*' | head
curl -s https://apartmanibihac.com/izleti-bihac/ | grep -c 'class="vp"'
