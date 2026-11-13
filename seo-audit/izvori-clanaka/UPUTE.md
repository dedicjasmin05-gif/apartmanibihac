# Upute za pisanje članaka za apartmanibihac.com

Pišeš za apartmanibihac.com: lokalnu stranicu za smještaj u Bihaću i uz rijeku Unu (BiH).
Na webu je 19 objekata (popis: `objekti.txt` u ovoj mapi). Postojeće stranice i članci su u `postojece-stranice.txt`: nemoj duplirati njihov sadržaj, nego ih linkaj.

## Cilj
Članci trebaju biti stvarno korisni gostima i takvi da ih Google i AI pretraživači (ChatGPT, Copilot, Perplexity) mogu citirati: konkretne, provjerene činjenice koje drugi webovi nemaju složene na jednom mjestu.

## Pravila (obavezno)
1. **Svaka činjenica provjerena** pretraživanjem weba (WebSearch / WebFetch) na službenim ili pouzdanim izvorima: NP Una, Granična policija BiH, MUP RH, EU (travel-europe.europa.eu), turističke zajednice, organizatori, HAK, BIHAMK, Google karte za udaljenosti. Ako nešto ne možeš potvrditi, **izostavi** ili napiši općenito („provjerite na …“). Nikad ne izmišljaj cijene, radno vrijeme, datume, brojeve telefona.
2. Udaljenosti i vremena vožnje navodi kao približne („oko 35 km, oko 40 minuta“).
3. Na kraju svakog teksta: odjeljak **Izvori** s linkovima koje si stvarno koristio i redak „Provjereno: 12. 11. 2026.“ (en: „Checked: 12 November 2026“, de: „Geprüft am 12. November 2026“).
4. Struktura: prvi odlomak je **kratak odgovor u 2–3 rečenice** (ono što AI preuzima), zatim podnaslovi `<h2>`, gdje ima smisla tablica (`<table>` s `<thead>`), te na kraju `<h2>` „Česta pitanja“ / „FAQ“ / „Häufige Fragen“ s 3–5 pitanja kao `<h3>` + `<p>`.
5. Duljina: 600–1.000 riječi po jeziku. Bez uskličnika, bez pretjerivanja („najbolji“, „savršen“), bez emotikona.
6. **Smještaj**: na kraju, gdje je tematski opravdano, 1 odlomak koji preporuči stvarno povezane objekte s linkom (npr. za rafting Green River i Vikendica Unadom u Grmuši; za NP Una Vila Enio u Lohovu; za grad apartmane u Bihaću). Samo objekti iz `objekti.txt`, s točnim podacima (mjesto, kapacitet, oprema). Ne izmišljaj cijene objekata; može: „cijeli apartman od 25 € po noći“ (opća tvrdnja weba).
7. Pravila weba koja smiješ navesti: smještaj se plaća gotovinom po dolasku; kapara nije obavezna, a ako pojedini objekt traži avans, to piše u potvrdi upita; odgovor na upit u roku od 12 sati; psi i mačke na upit; WhatsApp/Viber +387 61 591 320.
8. Linkovi na naš web su relativni (`/smjestaj/vila-enio/`, `/izleti-bihac/`, `/rafting-na-uni/`, `/cesta-pitanja/`, `/en/faq/`, `/de/faq/`, `/smjestaj-bihac/`, `/en/accommodation-bihac/`, `/de/unterkunft-bihac/`). Vanjski linkovi: `target="_blank" rel="noopener"`.
9. Jezik:
   - **bs**: bosanski standard kakav je na webu (ijekavica, „smještaj“, „rezervacija“, „sedmica“ ili „tjedan“ je u redu), prirodno, ne prijevod s engleskog.
   - **en**: za strane goste (UK/US/Nizozemska/Skandinavija), objasni što Bosancu nije potrebno (valuta KM, granica, dokumenti).
   - **de**: za goste iz Njemačke, Austrije i Švicarske te dijasporu; „Sie“ oblik.
   - en/de **nisu doslovni prijevod**: prilagodi čitatelju (npr. vožnja iz Münchena/Beča).
10. Dopušteni HTML: p, h2, h3, ul, ol, li, table, thead, tbody, tr, th, td, a, strong, em. Bez `<h1>` (naslov ispisuje tema), bez stilova, bez slika.

## Izlaz
Za svaku temu zapiši **jednu JSON datoteku** `<bs-slug>.json` u ovu mapu, UTF-8, ovog oblika:

```json
{
  "bs": {"slug": "bs-slug-bez-dijakritika", "kat": "izleti|bihac|smjestaj", "naslov": "Naslov do 60 znakova", "izvod": "Opis za Google, 120–155 znakova", "html": "<p>...</p>...", "slika": "kljuc-fotografije"},
  "en": {"put": "en/guide/en-slug", "title": "SEO naslov do 60 znakova", "meta": "120–155 znakova", "h1": "Naslov stranice", "html": "<p>...</p>...", "faq": [{"q": "...", "a": "..."}]},
  "de": {"put": "de/reisefuehrer/de-slug", "title": "SEO-Titel bis 60 Zeichen", "meta": "120–155 Zeichen", "h1": "Seitentitel", "html": "<p>...</p>...", "faq": [{"q": "...", "a": "..."}]}
}
```

- `faq` u en/de = ista pitanja kao u HTML-u (za FAQ schemu). U bs ne treba zasebno polje (FAQ je u HTML-u).
- `slika` (samo bs) je jedan od ključeva fotografija: np-una-posjeta, strbacki-buk, martin-brod, milancev-buk, kulen-vakuf, lohovo-troslap-dvoslap, ripac, japodski-otoci, golubic, pritoka, tvrdjava-sokolac, kostela-kostelski-buk, kanjon-une-bihac-krupa, bihac-una-otoci, kapetanova-kula, fethija-dzamija, ostrozac, cazin, sturlic, pecigrad, buzim, bosanska-krupa, vrelo-krusnice, bosanska-otoka, una-rafting, ribolov-una, biciklizam-una, zeljava, pljesevica, grmec, plitvicka-jezera, rastoke-slunj, vrelo-une.
- Provjeri da je JSON ispravan (`python3 -c "import json;json.load(open('...'))"`).

Na kraju vrati kratak sažetak: koje datoteke si napisao, i popis tvrdnji koje NISI mogao potvrditi (pa si ih izostavio).

## Jezik: bosanski, ne hrvatski (Jasmin, 13. 11. 2026.)
Piši bosanskim standardom, bez hrvatskih riječi:
sistem (ne sustav), pasoš (ne putovnica), lična karta (ne osobna iskaznica), dokument (ne isprava),
granični prelaz (ne prijelaz), ko (ne tko), šta (ne što u pitanjima), voz (ne vlak), avion (ne zrakoplov),
aerodrom (ne zračna luka), hiljada (ne tisuća), sedmica (ne tjedan), porodica (radije nego obitelj),
elektronski (ne elektronički), dozvoljeno (ne dopušteno), vanredno (ne izvanredno), nosilac (ne nositelj),
postepeno (ne postupno), mjeseci: januar, februar, mart, april, maj, juni, juli, august, septembar, oktobar, novembar, decembar
(i u slugovima: npr. ...-novembar-2026, nikad studeni). Naslovi izvora i vlastita imena ostaju kako jesu.

## Puni SEO i prijevodi za SVAKU vijest (Jasmin, 13. 11. 2026.)
Svaka nova vijest (dnevna i sedmična) ide na tri jezika i mora biti spremna za indeksiranje:
- bs: `naslov` ≤ 60 znakova, ključna riječ naprijed (npr. „Bihać: …“, „NP Una: …“); `izvod` 140–155 znakova (meta opis, poziv na klik);
  `html`: prvi odlomak odgovara na „šta, gdje, kada“ i spominje Bihać/Unu/USK; 2–3 podnaslova `<h2>`; barem 1 unutarnja poveznica
  na relevantan vodič ili smještaj na apartmanibihac.com; izvor na kraju; bez izmišljenih podataka.
- `en` i `de`: objekti s istim poljima {slug, naslov, izvod, html}, prirodan prijevod (ne doslovan), isti podaci, slug na tom jeziku
  (ASCII, s „bihac“ gdje prirodno), naslov ≤ 60, izvod 120–155, poveznice na EN/DE stranice (seo/parovi.json, /en/stays/, /de/unterkuenfte/,
  /en/guide/, /de/reisefuehrer/), izvor na kraju („Source:“ / „Quelle:“). Datumi: „14 November 2026“ / „14. November 2026“.
- Web sam napravi /en/news/<slug>/ i /de/nachrichten/<slug>/, hreflang između sve tri verzije, NewsArticle schema, sliku s logom i IndexNow.
