#!/bin/sh
P=/home/u227550228/domains/apartmanibihac.com/public_html/wp-content/plugins/apartmani-upiti
php $P/alati/okomite.php | tail -25
curl -s https://apartmanibihac.com/izleti-bihac/ | grep -o 'vodic.css?ver=[0-9.]*\|main.css?ver=[0-9.]*' | sort -u
