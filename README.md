# Dnevne vijesti iz turizma (apartmanibihac.com)

Ova grana sadrži samo `sadrzaj/dnevne-vijesti.json`. Web (modul `dnevne-vijesti.php` na grani `main`) čita datoteku svakih 15 minuta i svaku vijest objavljuje kad dođe njeno vrijeme (`objavi`, ISO s vremenskom zonom), u rubriku „Vijesti iz turizma“. Zatim je modul Društvene mreže dijeli na Facebook i Instagram.

Polja: `slug`, `naslov` (do 60 znakova), `izvod` (120–155), `html` (vlastiti kratki tekst; na kraju izvor), `objavi` (npr. `2026-11-14T08:30:00+01:00`), `izvor_url`, `slika` (ključ fotografije iz vodiča), `fb`, `ig`.
Pravila: samo provjerene vijesti, vlastiti sažetak (bez kopiranja tuđeg teksta i slika), uvijek link na izvor, najviše 4 dnevno.
