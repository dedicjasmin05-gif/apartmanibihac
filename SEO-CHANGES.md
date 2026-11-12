# SEO izmjene: grana `ccr-ab0db569-mim4o1`

**Objavljeno na produkciju 11. 11. 2026. u 22:52 UTC** (dodatak 1.1.4, tema 1.1.8; 28 datoteka) uz odobrenje vlasnika. Rezultati provjere: `seo-audit/provjera-prije-2026-11-11.txt` → `seo-audit/provjera-poslije-2026-11-11.txt` (20/20 točaka prema očekivanju). Ponovni prolaz cijelog sitemapa: 183 URL-a, svi 200, nijedna stranica s dva H1, jedan meta opis po stranici.

**Povratak:** datoteke prije objave identične su grani `main` na commitu `0189713` (dodatak) i grani `tema` na `f20ae7d`. Te datoteke treba ponovno učitati; izmijenjene stranice u bazi imaju WordPress revizije.

**Ništa od ovoga nije objavljeno na produkciji.** Svaka izmjena je mala i ima vlastiti povrat.

Testovi bez WordPressa: `php testovi/test-seo-tehnika.php` (24/24 ✓), `php testovi/test-povezivanje.php` (49/49 ✓; s `-v` ispisuje odabir), `php testovi/test-mjerenje.php` (15/15 ✓). JavaScript mjerenja provjeren u Nodeu: 11 izvora dolaska i 5 vrsta klikova točno prepoznato. Provjera sintakse: `php -l` za sve datoteke ✓. Zakrpa teme se čisto primjenjuje: `git apply --check` na `origin/tema` ✓.

## Dodatak (ova grana)

| # | Datoteka | Izmjena | Nalaz | Povrat |
|---|---|---|---|---|
| 1 | `seo-tehnika.php` §1 (novo) | Ako u spremljenim pravilima adresa nema `^smjestaj/?$` ili `oprema/([^/]+)/?$`, pravila se jednom osvježe (najviše jednom u 10 min); vrijeme se bilježi u opciji `abu_rw_popravljeno` | 1, 2 | ukloniti blok §1 |
| 2 | `seo-tehnika.php` §2 | `noindex,follow` za `/smjestaj/` pretragu, arhive `/oprema/…` i `/header-slike/`; oprema i `header-slike` izbačeni iz `wp-sitemap.xml` | 2 | ukloniti blok §2 |
| 3 | `seo-tehnika.php` §3 | Pri prikazu uklanja **samo** početni `<h1>` u sadržaju koji je identičan naslovu stranice | 5 | ukloniti blok §3 |
| 4 | `seo-tehnika.php` §4 | `BlogPosting` i `BreadcrumbList` JSON-LD na člancima (naslov, datumi, slika, izdavač `#org`) | 10 | ukloniti blok §4 |
| 5 | `seo-tehnika.php` §5 | Linkovi `/vodic/naziv/` u člancima ispisuju se na stalnu adresu članka; nepoznati ostaju isti | 8 | ukloniti blok §5 |
| 6 | `jezici.php` | hreflang naslovnice samo na `/`, `/en/` i `/de/` | 4 | `git revert` ili vratiti uvjet `!is_front_page() && !abu_jezik_stranice()` |
| 7 | `seo.php`, `uvoz.php` | „do 3 osoba“ → „do 3 osobe“ (`abu_osobe()`) u naslovima objekata i llms.txt | 6 | vratiti `' osoba'` |
| 8 | `uvoz.php` | llms.txt na `init` 100 umjesto 1, pa sadrži stalne adrese umjesto `?p=ID` | 9 | vratiti `}, 1);` |
| 9 | `apartmani-upiti.php` | `require_once seo-tehnika.php` | — | obrisati jedan redak; time se vraćaju sve izmjene 1–5 |
| 10 | `povezivanje.php` (novo) | **Tematsko povezivanje:** na kraju 41 članka i 72 stranice tema pitanja (24 teme × bs/en/de) blok s 3 povezana objekta i linkom na sav smještaj. Objekti se biraju iz stvarnih podataka (mjesto, oprema, kapacitet), ne iz teksta. Redoslijed ovisi o stranici, pa se linkovi raspoređuju na svih 19 objekata. Objekt već linkan u tekstu se preskače. Za ljubimce bloka nema, jer pravila objekata za pse nisu potvrđena | 8 | ukloniti `require_once povezivanje.php` |
| 11 | `mjerenje.php` (novo) | **Mjerenje bez kolačića:** brojanje klikova na telefon, WhatsApp, Viber i e-mail te izvora dolaska (Google, Bing, ChatGPT, Copilot, Perplexity, Gemini, Claude, Facebook…). Sprema se samo mjesečni zbroj (opcija `abu_mjerenje_GGGG-MM`), bez IP adrese, kolačića i podataka u pregledniku. Botovi i prijavljeni urednici se ne broje. Izvještaj je u adminu pod **Upiti → Mjerenje**; upiti s weba i potvrđene rezervacije čitaju se iz postojeće tablice `abu_upiti`, a team building upiti iz `tb_upit` | P1.1 | ukloniti `require_once mjerenje.php`; opcije `abu_mjerenje_*` se mogu obrisati |
| 12 | `seo-tehnika.php` §6 | Slika bez alt teksta dobiva naslov objave kojoj pripada (logotipi partnera na team buildingu, vijesti). Ručno upisan alt ostaje | 12 | ukloniti blok §6 |
| 13 | `seo-tehnika.php` §7 | Kad naslov već sadrži Bihać ili Unu, ne dodaje se „– Apartmani i vikendice Bihać“ (44 naslova kraća za 30 znakova). Naslovnica i ručno postavljeni naslovi ostaju | 14 | ukloniti blok §7 |
| 14 | `preusmjeravanja.php` | robots.txt: AI botovi dobivaju iste zabrane kao ostali (`/wp-admin/`, `/?s=`, `/*?abu_a=`), i dalje uz `Allow: /` | 11 | vratiti stari tekst (`git revert`) |
| 15 | `drustvene.php` | Na naslovnici se ne ispisuje drugi poslovni entitet (`LodgingBusiness`) kad tema ima svoj (`#org`); cijena i poštanski broj prelaze u entitet teme | 10 | ukloniti `&& !function_exists('ab_faq')` |
| 16 | `pitanja/*.json` (bs/en/de), `seo/stranice.json`, `sadrzaj/vijesti.json`, `uvoz.php` | **Jedno pravilo plaćanja:** „Smještaj plaćate gotovinom po dolasku; kapara nije obavezna, a ako pojedini objekt traži avans, to piše u potvrdi upita prije bilo kakve uplate.“ Uklonjeno je obećanje „avans uz popust“ (12 pitanja × 3 jezika, 14 mjesta na SEO stranicama, 1 članak, llms.txt). U njemačkim odgovorima uklonjene su i ponovljene rečenice i ostaci ranije djelomične zamjene. Popust za dulji boravak („nije zajamčen“) ostaje | 13 | `git revert` |
| 17 | `uvoz.php`, `alati/zamjene-placanje-3.json` | Jednokratna zamjena istih rečenica u već objavljenom sadržaju u bazi (opcija `abu_mig_placanje3`), istim mehanizmom kao migracije 1 i 2 | 13 | izmijenjene stranice imaju WordPress reviziju |
| 18 | `seo/stranice.json` | **Nova stranica „O nama“** (`/o-nama/`, isti URL): tko smo, kako radimo (vlasnici, upit i potvrda, plaćanje, ljubimci, adresa nakon potvrde), ponuda (19 objekata), kontakt. Samo činjenice koje su već na webu. Stari tekst o Optici Dervišević ostaje u revizijama stranice. Naslov, meta opis i FAQ schema (3 pitanja); stranica dodana i u llms.txt | 13 | vratiti reviziju u WordPressu (Stranice → O nama → Revizije) ili ukloniti ključ `o-nama` iz JSON-a |
| 19 | `povezivanje.php` | Ljubimci: tema „Putovanje s ljubimcem“ (bs/en/de) i članak o psu dobivaju blok „Smještaj s psom ili mačkom“ s napomenom „psi i mačke na upit… vlasnik potvrđuje uz odgovor, zajedno s eventualnom doplatom“ | — | — |
| 20 | `seo-tehnika.php` §3 (dorada nakon objave) | Dupli H1 uklanja se i kad stoji iza putanje ili oznake u tekstu (stranice lokacija i članci od 21. 10.), ne samo na početku | 5 | — |
| 21 | `seo.php` (dorada nakon objave) | Stranice vodiča (restorani, izlasci, izleti, rafting) i team buildinga ispisuju vlastiti meta opis, pa `seo.php` za njih više ne dodaje drugi (15 stranica). Ova greška postojala je i prije, ali ju je skrivao dupli opis iz teme | 3 | — |
| 22 | `seo-tehnika.php` §2, `uvoz.php` | Članak iz 2024. („Naslov: Pronađite odličan i jeftin smještaj…“) ima `noindex,follow`, nije u sitemapu ni u llms.txt; adresa i tekst ostaju | 13 | ukloniti slug iz `abu_seo_skriveni_clanci()` |
| 23 | `apartmani-upiti.php` | Verzija 1.1.4 (osvježavanje pravila adresa, IndexNow šalje sve URL-ove jednom) | — | — |

## Tema (`zakrpe/tema-1.1.8.patch`, primjenjuje se na granu `tema`)

| Izmjena | Nalaz |
|---|---|
| Tema ne ispisuje meta opis kad je aktivan dodatak (postoji `abu_seo_tekuca`); bez dodatka radi kao prije | 3 |
| `ab_kartica()`: osobe, cijena, oprema i „slobodno“ na EN/DE prema `lang`; za bs isto kao prije | 7 |
| `ab_osobe()` vraća cijeli izraz („do 3 osobe“ / „up to 3 guests“ / „bis 3 Personen“), a pozivi su prilagođeni (`functions.php`, `single-objekt.php`) | 7 |
| Schema `TravelAgency`: `sameAs` = stvarni FB i IG profili (`ab_fb()`, `ab_ig()`); prazan `email` se ne ispisuje | 10 |
| Schema `TravelAgency`: dodani `postalCode` 77000 i `priceRange` (od cijene iz postavki do 100 €), jedan poslovni entitet umjesto dva | 10 |
| Stranica objekta: u retku činjenica „psi i mačke na upit“; u schemi objekta `petsAllowed` | — |
| Podnožje: link „O nama“ (stranica nije imala nijedan ulazni link) | 8 |
| „9.362 recenzija **na Facebooku**“ u gornjoj traci, bloku povjerenja i na naslovnici (bs/en/de) | 13 |
| Verzija 1.1.8, da se osvježe CSS i JS u cacheu i pravila adresa | — |

Povrat teme: vratiti datoteke verzije 1.1.7 s grane `tema` (`git checkout origin/tema -- .`).

## Kako provjeriti nakon objave (prije / poslije)

| Provjera | Prije (11. 11.) | Očekivano poslije |
|---|---|---|
| `curl -sI "https://apartmanibihac.com/smjestaj/?mjesto=Lohovo"` | 301 → /smjestaj-bihac/ | **200**, samo objekti u Lohovu |
| Pretraga s naslovnice (mjesto + osobe + datumi), ručno na mobitelu | svih 19 objekata | filtrirano |
| `curl -s -o /dev/null -w '%{http_code}' https://apartmanibihac.com/oprema/bazen/` | 404 | 200 s `noindex` |
| `curl -s https://apartmanibihac.com/wp-sitemap.xml \| grep -c oprema` | 1 | 0 |
| Broj `<meta name="description">` na naslovnici i objektu | 2 | 1 (nakon teme 1.1.8) |
| hreflang u `<head>` na `/en/faq/una-river/` | 9 (proturječni) | 4 (bs, en, de, x-default) |
| Broj `<h1>` na `/smjestaj-lohovo/` i članku od 21. 10. | 2 | 1 |
| `<title>` objekta Apartman Centar | „…do 3 osoba“ | „…do 3 osobe“ |
| `curl -s https://apartmanibihac.com/llms.txt \| grep -c '?p='` | 19 | 0 |
| Kartice na `/en/` | „do 3 osobe · Kuhinja · cijena na upit“ | „up to 3 guests · Kitchen · price on request“ |
| JSON-LD na članku | nema `BlogPosting` | `BlogPosting` i `BreadcrumbList` (Rich Results Test ✓) |
| Linkovi na objekte iz članaka i pitanja | 0–2 po stranici; 113 stranica bez ijednog | 3 po stranici + link na sav smještaj |
| `<title>` članka „Prvi rafting na Uni: kako odabrati turu“ | „… – Apartmani i vikendice Bihać“ (69 znakova) | bez dodatka (39 znakova) |
| Poslovni entiteti u schemi naslovnice | 2 (`TravelAgency` i `LodgingBusiness`, različita imena) | 1 (`TravelAgency #org`) |
| Upiti → Mjerenje u adminu | ne postoji | brojke se pune od prvog dana |
| `/o-nama/` | tekst o Optici Dervišević, 0 ulaznih linkova | stranica o smještaju, link u podnožju |
| Spominjanje „avans uz popust“ (bs/en/de) | 33 rečenice na 15 stranica (provjera živog weba 11. 11.) | 0 |
| Forma upita na objektu (`form.abu-form`, POST na `admin-post.php`) | radi | radi, **bez promjene** |

Ponovno pokretanje cijele inventure: skripta `seo-audit/crawl.py` zapisuje `crawl.json`; usporediti s `seo-audit/inventura-2026-11-11.csv`.

**Dodatak i temu objaviti zajedno.** Ako se dodatak objavi bez teme 1.1.8, s naslovnice nestaje drugi poslovni entitet, a entitet stare teme ostaje bez cijene i profila.

## Nije mijenjano (namjerno)

- HTML `<title>` naslovnice je ispravan.
- OG naslovnice rješava PR #3, ovdje nije dupliran.
- Sadržaj u bazi (članci, pitanja, `/o-nama/`), URL-ovi, preusmjeravanja, dizajn, logika rezervacija i e-mailovi.

## Dodatak 1.1.5 (11. 11. 2026., nakon čitanja baze)

Čitanje baze (`alati/seo-stanje.php`, samo čitanje, jednokratni cron posao je obrisan) pokazalo je sljedeće:
- u studenom 1 upit s weba, i taj je **istekao bez odgovora**;
- 7 team building upita;
- svih 19 objekata bez e-maila vlasnika, bez iCal linkova i bez cijene;
- Japodska kućica i Villa Mekić bez fotografija;
- prazno polje za Google recenzije.

| Datoteka | Izmjena | Povrat |
|---|---|---|
| `apartmani-upiti.php` | Kad upit istekne bez odgovora, gost **više ne dobiva** automatski e-mail „nažalost, objekt nije slobodan“ (to nitko nije provjerio). Admin dobiva hitan e-mail s kontaktom gosta i linkovima Potvrdi/Odbij; istekli upit može se naknadno potvrditi ili odbiti | `git revert` |
| `apartmani-upiti.php` | Poruka nakon potvrde kod plaćanja gotovinom: „gost je dobio potvrdu rezervacije“ umjesto „upute za kaparu“ | — |
| `mjerenje.php` | Jednokratno brisanje brojača za 2026-11 (sadržavao je naše automatske provjere s dana objave); mjerenje kreće od nule | — |


## 12. 11. 2026.: popravci prema provjeri s MAG servera, LiteSpeed Cache, WebP

| Što | Izmjena | Provjera na živom webu |
|---|---|---|
| Slike objekata na 5 stranica lokacija (404) | `seo-tehnika.php` §8: `/assets/*.webp` sa starog sajta zamijenjene fotografijom objekta na koji kartica vodi | 0 neispravnih slika |
| Mrtvi linkovi restorana | `sadrzaj/restorani.json`: uklonjeni `riverdock.ba` i `riveruna.com.ba` (FB i IG ostaju) | link više ne postoji |
| og:image | `drustvene.php`: zamjenska slika s naslovnice kad stranica nema svoju | og:image na svim stranicama |
| Kategorije | `seo-tehnika.php` §9: meta opis i canonical | da |
| Tema 1.1.9 | bez `backdrop-filter` na mobitelu i tabletu; nazivi skrivenih linkova galerije | izgled isti, bez zamućenja |
| **LiteSpeed Cache 7.9.3** | instaliran i aktivan (Hostinger API); `cache.php` isključuje iz cachea objekte, pretragu, prijavu objekta, team building i potvrde iz e-maila (sigurnosni ključ forme) | naslovnica, pitanja, O nama: `hit`; objekti i forme: `no-cache`; 48 istodobnih zahtjeva bez ijedne greške 500 |
| **WebP** | `webp.php` + jednokratno `alati/webp-sve.php`: 2.430 WebP kopija od 2.629 slika (−57 MB); stranice poslužuju WebP, og:image ostaje JPG, nove slike automatski | naslovnica: 50 WebP, 0 JPG u tijelu |

Povrat: LiteSpeed Cache se može deaktivirati u WP adminu (Plugins). WebP: ukloniti `require_once webp.php` (stranice se vraćaju na JPG, a `.webp` datoteke mogu ostati). Cache: ukloniti `require_once cache.php`, ali samo ako se deaktivira i LiteSpeed.

Rezultat provjere u 20 točaka: `seo-audit/provjera-2026-11-12.txt` (20/20). Cijeli sitemap: 183/183 vraća 200.
