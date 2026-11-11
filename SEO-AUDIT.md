# SEO i AI audit: apartmanibihac.com

**Datum:** 11. 11. 2026.
**Način rada:** pročitan je kod dodatka (ova grana), kod teme (grana `tema`, iste veličine kao datoteke na serveru), datoteke na hostingu (Hostinger API, samo čitanje) i javni HTML. Sitemap je pročitan cijeli: **196 URL-ova**, svaki dohvaćen jednom.
**Ništa nije mijenjano na produkciji.** Nije poslan nijedan upit ni rezervacija.

Potpuna inventura (title, meta opis, OG, canonical, H1, hreflang, schema, broj riječi, ulazni linkovi): [`seo-audit/inventura-2026-11-11.csv`](seo-audit/inventura-2026-11-11.csv).

Oznake: **POTVRĐENO** znači da je problem vidljiv u stvarnom HTML-u ili odgovoru servera i da je u kodu pronađen njegov izvor. **ZA PROVJERU** znači da nalaz postoji, ali iz ovog okruženja ne može se dokazati.

---

## 0. Raniji rad koji postoji: ne duplirati

| Što | Gdje | Stanje |
|---|---|---|
| Naslovi, meta opisi, FAQPage schema za SEO stranice (bs/en/de) | `seo.php`, `seo/stranice.json` | radi |
| 1.000 pitanja × 3 jezika, stranice tema, `llms-full.txt` | `pitanja.php`, `pitanja/` | radi; činjenice provjerene 1. 11. (`pitanja/PROVJERENO.md`), otvorene stavke u `pitanja/ZA-PROVJERU.md` |
| robots.txt s pravilima za AI botove, `llms.txt` | `preusmjeravanja.php`, `uvoz.php` | radi (vidi nalaz 9) |
| IndexNow (Bing, Yandex, Seznam, Naver) | `indexnow.php` | ugrađen 7. 11.; ne treba novi |
| 301 sa starog sajta | `preusmjeravanja.php` | radi |
| Kratki odgovor za AI na „jeftin smještaj“ | `ai-odgovori.php` | radi |
| Ispravak OG naslova i opisa naslovnice | **otvoreni PR #3** (`codex/ai-search-og-home-minimal`) | pregledan, ispravan, **čeka spajanje** |
| Read-only audit alati i praćenje AI vidljivosti (32 upita) | PR #2 i PR #4 (`ai-lab/`) | čekaju spajanje |

**„Početna“ nije SEO naslov.** HTML `<title>` naslovnice je ispravan: *Smještaj Bihać: apartmani i vikendice na Uni od 25 €*. Riječ „Početna“ dolazi iz `og:title`, koji `drustvene.php` puni naslovom WordPress stranice, a `og:description` je prazan. Ovo već rješava PR #3, pa ovdje nije rađeno ponovno.

---

## 1. Kritično: pretraga smještaja s naslovnice ne filtrira (POTVRĐENO)

**Dokaz**
```
GET /smjestaj/?mjesto=Lohovo&osobe=4
→ 301 (x-redirect-by: WordPress) → /smjestaj-bihac/?mjesto=Lohovo&osobe=4   (prikazuje svih 19 objekata)
GET /?ab_smjestaj=1&mjesto=Lohovo  → 200, „Smještaj Lohovo“, 1 objekt        (predložak teme radi)
```
Obje forme na naslovnici (`search--hero`, `search--mid`) šalju na `/smjestaj/`. Gost koji odabere mjesto, broj osoba i datume dobije nefiltriran popis.

**Uzrok.** U spremljenim pravilima adresa nema pravila teme `^smjestaj/?$`. Zato WordPress `/smjestaj/` smatra nepostojećom stranicom i „pogodi“ najsličniju (`smjestaj-bihac`). Isti uzrok daje nalaz 2. Pravila se osvježavaju u `apartmani-upiti.php` (init 99) samo kad se promijeni verzija dodatka ili teme. Najvjerojatnije su spremljena dok tema nije bila potpuno učitana, primjerice tijekom prijenosa datoteka.

**Popravak:** `seo-tehnika.php` §1.

## 2. 11 URL-ova u sitemapu vraća 404 (POTVRĐENO)

`/oprema/bazen/`, `/oprema/kuhinja/`, `/oprema/klima/` … (svih 11 iz `wp-sitemap-taxonomies-oprema-1.xml`). Isti uzrok kao nalaz 1. `/?taxonomy=oprema&term=bazen` preusmjerava na `/oprema/bazen/`, a ta adresa daje 404. Search Console takve URL-ove prijavljuje kao „Poslano, ali nije pronađeno“.

Kad se pravila poprave, te arhive imaju samo oko 24 riječi i karticu objekta, pa su slab sadržaj. Za opremu s potražnjom već postoje kvalitetne stranice (`/vikendice-s-bazenom/`, `/smjestaj-uz-unu/`). **Popravak:** pravila adresa, `noindex,follow` i izbacivanje iz sitemapa (`seo-tehnika.php` §1–2).

## 3. Dva meta opisa na 176 od 196 stranica (POTVRĐENO)

Dodatak (`seo.php`, wp_head 2) i tema (`functions.php`, wp_head 5) ispisuju po jedan `<meta name="description">`. Na naslovnici su tekstovi različiti, a na objektima i člancima isti. Google tada sam bira jedan ili oba ignorira.

Opis iz dodatka je bolji: uređeni opisi za SEO stranice i pitanja, a uklanja i kratke kodove. Tema bez `strip_shortcodes` može ispisati `[abu_lista]` u opisu. **Popravak:** tema preskače svoj opis kad je dodatak aktivan (`zakrpe/tema-1.1.8.patch`).

## 4. Proturječni hreflang na svim /en/ i /de/ podstranicama (POTVRĐENO)

Primjer `/en/faq/una-river/` ima u `<head>` dva skupa oznaka:
```
hreflang="bs" → /                         hreflang="bs" → /cesta-pitanja/rijeka-una/
hreflang="en" → /en/                      hreflang="en" → /en/faq/una-river/
hreflang="x-default" → /                  hreflang="x-default" → /cesta-pitanja/rijeka-una/
```
Prvi skup (`jezici.php`) ispisuje se na svakoj stranici ispod /en/ i /de/ i tvrdi da je prijevod svake podstranice naslovnica. Pogrešno je i proturječi ispravnom drugom skupu, pa Google može odbaciti oba. Zahvaćeno je oko 70 stranica. **Popravak:** skup naslovnice samo na `/`, `/en/` i `/de/` (`jezici.php`).

## 5. Naslov prikazan dvaput, dva H1 (POTVRĐENO, 24 stranice)

Tekst u bazi počinje s `<h1>` jednakim naslovu, a tema već ispisuje naslov kao H1. Posjetitelj vidi naslov dvaput. Zahvaćeno je 19 članaka od 21. 10., `/smjestaj-lohovo/`, `/smjestaj-pritoka/`, `/smjestaj-grmusa/`, `/smjestaj-uz-unu/` i `/vikendice-s-bazenom/`. **Popravak:** pri prikazu se uklanja samo početni H1 koji je identičan naslovu (`seo-tehnika.php` §3). Baza ostaje ista.

## 6. Gramatika u naslovima objekata i llms.txt (POTVRĐENO)

„Apartman Centar: smještaj Bihać, **do 3 osoba**“, „do 4 osoba“, „do 22 osoba“. Ispravno je *do 3 osobe*. Takav naslov se vidi u Googleu. **Popravak:** `abu_osobe()` u `seo.php` i `uvoz.php`.

## 7. Engleska i njemačka verzija: kartice objekata na bosanskom (POTVRĐENO)

Na `/en/` i `/de/` kartice pokazuju „do 3 osobe“, „Kuhinja“ i „cijena na upit“. `ab_kartica()` u temi ne zna jezik, iako dobiva `lang`. **Popravak:** prijevodi oznaka u `zakrpe/tema-1.1.8.patch`. URL-ovi i raspored ostaju isti.

Stranica objekta s `?lang=en` i dalje je na bosanskom, a canonical pokazuje na bosansku stranicu. To nije duplikat, ali EN/DE gost iz kartice dolazi na bosanski tekst. Pravi prijevodi stranica objekata su sadržajni posao (plan P2).

## 8. Interni linkovi preko 301 i slabo povezani članci (POTVRĐENO)

- 17 različitih linkova u člancima vodi na `/vodic/naziv/`, a svaki je 301 na `/2026/10/21/naziv/`. **Popravak:** pri prikazu link pokazuje na konačnu adresu (`seo-tehnika.php` §5).
- 24 od 42 članka imaju 0–1 izravni ulazni link. 75 stranica s pitanjima ima samo jedan, s indeksne stranice pitanja. Smještaj, članci i pitanja nisu međusobno povezani po temi (plan P1.4).
- `/o-nama/` nema nijedan ulazni link.

## 9. llms.txt vodi objekte na `?p=ID` (POTVRĐENO, niska važnost)

Handler radi na `init` prioritetu 1, prije registracije objekata, pa `get_permalink()` vraća `?p=4`. Svih 19 adresa daje 301 na ispravnu adresu. **Popravak:** prioritet 100 (`uvoz.php`).

## 10. Structured data (POTVRĐENO)

| Stranice | Sada | Nedostaje / problem |
|---|---|---|
| Naslovnica | `WebSite`, `TravelAgency`, `FAQPage` (tema) i zaseban `LodgingBusiness` (dodatak) | dva poslovna entiteta s različitim imenom („Apartmani i vikendice Bihać“ / „Apartmani & Vikendice Bihać“); `TravelAgency` ima `"email":""` i `"sameAs":[]` |
| Objekti (19) | `LodgingBusiness`, `BreadcrumbList`, oprema | u redu; bez izmišljenih ocjena |
| Članci (42) | samo `WebSite` i `TravelAgency` | **nema `BlogPosting` ni breadcrumba** |

**Popravci:** `BlogPosting` i `BreadcrumbList` za članke (`seo-tehnika.php` §4). U temi stvarni Facebook i Instagram profili u `sameAs`, a prazan e-mail se ne ispisuje. Spajanje dva poslovna entiteta u jedan je u planu (P1.3), jer traži odluku o imenu.

**VacationRental:** Google bogate rezultate za smještaj prikazuje samo partnerima Hotel Centera. Sama schema ih ne uključuje, pa ovdje nije dodana.

## 11. Pristup pretraživača i AI botova

| Provjera | Rezultat |
|---|---|
| robots.txt | dopušta Googlebot, Bingbot, OAI-SearchBot, GPTBot, ChatGPT-User, Claude-SearchBot, ClaudeBot, PerplexityBot; blokira samo `/wp-admin/`, `/?s=` i `/*?abu_a=` |
| HTML bez JavaScripta | opisi, kapacitet, oprema, kontakt i forma su u izvornom HTML-u ✓ |
| Googlebot / OAI-SearchBot / Claude-SearchBot / PerplexityBot UA | 200 |
| **Bingbot UA** | **403 Forbidden (LiteSpeed/Hostinger), i za robots.txt** — ZA PROVJERU |
| **GPTBot UA** | **429 Too Many Requests** — ZA PROVJERU |

Test je s tuđe IP adrese, a zaštita hostinga može namjerno blokirati lažne botove. Ipak, ako Hostinger blokira i pravi Bingbot, Bing ne indeksira web. O Bingovom indeksu ovise **Copilot i dio ChatGPT pretrage**. **Hitno provjeriti:** Bing Webmaster Tools → URL Inspection → *Live URL* te hPanel → Sigurnost / AI crawleri / zaštita od botova.

Napomena o robots.txt: posebne grupe za AI botove sadrže samo `Allow: /`, pa za njih ne vrijede zabrane iz grupe `*`. Šteta je mala (/?s= i /wp-admin/), ali je navedeno u planu.

## 12. Brzina i slike (izmjereno s ovog servera, bez terenskih CWV podataka)

- TTFB je 0,52–0,93 s (prosjek 0,67 s preko 196 URL-ova), a HTML 55–65 KB. Nema LiteSpeed cache zaglavlja, pa se stranice ne poslužuju iz page cachea.
- Naslovna slika ima `fetchpriority="high"`, `srcset` i nije lazy. Ostale slike su lazy, a na objektu prva je `eager`. Dobro postavljeno.
- Glavne fotografije su 46–133 KB. 116 slika ima prazan `alt`: sličice uz vijesti i partnere, uz koje odmah stoji vidljiv naslov. To je namjerno i ispravno (čitač zaslona ne čita isti naziv dvaput), pa nije mijenjano.
- PageSpeed API je vratio 429 bez ključa. **Terenske CWV vrijednosti treba pročitati u Search Consoleu (Core Web Vitals) ili PSI s ključem.**

## 13. Sadržaj i povjerenje

- **`/o-nama/` govori o „Optici Dervišević“**, ne o apartmanima. Vjerojatno je stranica prenesena sa starog sajta. To šteti povjerenju (E-E-A-T). **Odluka vlasnika**, nije mijenjano.
- **Stari članak iz 2024.** `/2024/09/04/naslov-pronadite-odlican-i-jeftin-smjestaj-u-bihacu-vodic-za-putnike/` ima naslov „Naslov: “…”“, neprikazan markdown (`###`, `**`) i opisuje hostele kojih nema u ponudi. Kandidat za doradu ili `noindex`. **Odluka vlasnika.**
- **Plaćanje i avans nisu usklađeni:**
  - *„avans ovisi o pravilima pojedinog objekta“*: FAQ na naslovnici, `seo/stranice.json`;
  - *„avans je dobrovoljan, uz popust“*: dio pitanja, llms.txt („uz avans popust“), jedan članak;
  - e-mail dodatka nakon potvrde šalje *„upute za kaparu… rok“* (kapara 30 %).

  Treba jedna istinita formulacija na svim mjestima. **Odluka vlasnika.**
- **„9.362 recenzije · 139.000 pratitelja · 100 % preporuka“** u traci na vrhu odnosi se na Facebook stranicu i vodi na nju. Nije označeno kao ocjena objekta niti je u schemi (`AggregateRating` ne postoji), što je ispravno. Brojke treba povremeno usporediti s Facebookom, a prijedlog za oznaku „na Facebooku“ je u planu.
- Pitanja: 1.000 po jeziku (25 tema × 40). Činjenice provjerene 1. 11. 2026., a otvorene točke (udaljenosti, cijene, Glovo i dr.) su u `pitanja/ZA-PROVJERU.md`.

## 14. Kanonske adrese, indeksiranje, preusmjeravanja

- www → 301 na apex; canonical na svim stranicama i objektima pokazuje na sebe ✓.
- `meta robots` je svugdje `max-image-preview:large`, bez slučajnog `noindex` ✓.
- Arhive kategorija i `/vodic/` nemaju canonical ni meta opis. To je uobičajeno za WordPress i nisko je važno.
- 44 naslova su duža od 70 znakova, jer se na kraj dodaje „– Apartmani i vikendice Bihać“. Google ih skraćuje. Prijedlog je u planu, nije mijenjano.

## 15. Nije moguće provjeriti iz ovog okruženja

- Google Search Console: upiti, klikovi, CTR, pozicije, indeksiranost, *Settings → Search generative AI* i generativni izvještaji.
- Bing Webmaster Tools: indeks, AI Performance (citiranja u Copilotu), stvarni pristup Bingbota.
- Analitika i mjerenje klikova na telefon, WhatsApp i Viber. U HTML-u nije pronađen GA4 ni drugi alat za analitiku: **nema mjerenja**.
- Sigurnosna kopija baze i testno okruženje (staging).

Za ovo je potreban pristup vlasnika. Upute su u `SEO-PLAN.md`.
