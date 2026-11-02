#!/bin/sh
# Pomoćna skripta za provjere preko Hostinger crona (mijenja se po potrebi).
T=/home/u227550228/domains/apartmanibihac.com/public_html/wp-content/themes/apartmanibihac
cat $T/.git/config; git -C $T log --oneline -5; git -C $T status --short | head
