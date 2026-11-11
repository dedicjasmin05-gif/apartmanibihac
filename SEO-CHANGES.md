# SEO izmjene: grana `ccr-ab0db569-mim4o1`

**Ništa od ovoga nije objavljeno na produkciji.** Svaka izmjena je mala i ima vlastiti povrat.

Testovi bez WordPressa: `php testovi/test-seo-tehnika.php` (24/24 ✓). Provjera sintakse: `php -l` za sve datoteke ✓. Zakrpa teme se čisto primjenjuje: `git apply --check` na `origin/tema` ✓.

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

## Tema (`zakrpe/tema-1.1.8.patch`, primjenjuje se na granu `tema`)

| Izmjena | Nalaz |
|---|---|
| Tema ne ispisuje meta opis kad je aktivan dodatak (postoji `abu_seo_tekuca`); bez dodatka radi kao prije | 3 |
| `ab_kartica()`: osobe, cijena, oprema i „slobodno“ na EN/DE prema `lang`; za bs isto kao prije | 7 |
| `ab_osobe()` vraća cijeli izraz („do 3 osobe“ / „up to 3 guests“ / „bis 3 Personen“), a pozivi su prilagođeni (`functions.php`, `single-objekt.php`) | 7 |
| Schema `TravelAgency`: `sameAs` = stvarni FB i IG profili (`ab_fb()`, `ab_ig()`); prazan `email` se ne ispisuje | 10 |
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
| Forma upita na objektu (`form.abu-form`, POST na `admin-post.php`) | radi | radi, **bez promjene** |

Ponovno pokretanje cijele inventure: skripta `seo-audit/crawl.py` zapisuje `crawl.json`; usporediti s `seo-audit/inventura-2026-11-11.csv`.

## Nije mijenjano (namjerno)

- HTML `<title>` naslovnice je ispravan.
- OG naslovnice rješava PR #3, ovdje nije dupliran.
- Sadržaj u bazi (članci, pitanja, `/o-nama/`), URL-ovi, preusmjeravanja, dizajn, logika rezervacija i e-mailovi.
- robots.txt.
