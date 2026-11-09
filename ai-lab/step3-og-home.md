# AI Search Lab — korak 3: minimalni Open Graph popravak

**Status: nacrt koda. Nije objavljeno na apartmanibihac.com.**

## Potvrđeno javnim HTML-om 9. 11. 2026.

| Vrsta stranice | HTML `<title>` | OG title | OG description | Meta description |
|---|---|---|---|---|
| Početna BS | Ispravan: „Smještaj Bihać: apartmani i vikendice na Uni od 25 €” | „Početna” — generički | Prazan | Dva, različita |
| Jeftin smještaj | Ispravan | Ispravan | Popunjen | Dva, identična |
| Apartman Centar | Ispravan | Ispravan | Popunjen | Dva, identična |
| /en/ | Ispravan | Ispravan | Popunjen | Dva, identična |
| /de/ | Ispravan | Ispravan | Popunjen | Dva, identična |
| Česta pitanja | Ispravan | Ispravan | Popunjen | Dva, različita |

## Točan izvor generičkog OG naslova

`drustvene.php` na `wp_head` koristi `get_the_title(get_queried_object_id())` i skraćeni sadržaj kao OG opis, a početna WordPress stranica naziva se „Početna” i nema sažetak.

## Što mijenja ovaj PR

1. `seo.php`: izvlači **potpuno neizmijenjen postojeći** SEO opis početne u funkciju `abu_seo_front_description()`. HTML SEO title, canonical, ostali meta opisi, schema i ostale stranice ostaju neizmijenjeni.
2. `drustvene.php`: **samo na početnoj** OG naslov uzima iz `wp_get_document_title()`, a OG opis iz iste provjerene SEO funkcije. Ostali URL-ovi zadržavaju prethodno ponašanje.
3. `ai-lab/tests/test-og-home.php`: testira frontpage naslov/opis, usklađenost s SEO opisom, nepromijenjeni apartman i ne-singularne stranice. Bez mreže, servera, baza, mailova i rezervacija.
4. GitHub Actions (samo ova grana/PR) izvodi PHP lint i offline test.

## Što namjerno NE mijenja

- Ne mijenja ni jednu rezervaciju, kalendar, WhatsApp, cijenu, postojeći URL, SEO title, dizajn, schema ili slike.
- **Ne uklanja dvostruke `meta name="description"` oznake** dok se ne identificira izvor dodatne oznake u aktivnoj temi `apartmanibihac`, čiji kod nije u ovom repozitoriju. Na početnoj i FAQ stranici oba opisa nisu jednaka; slijepo brisanje moglo bi ukloniti bolji.
- Ne uvodi nijedan vanjski SEO servis, plaćeni API, AI crawler niti novi WordPress dodatak.

## Kriteriji prije objave

- [ ] PHP CI mora proći.
- [ ] Na izoliranom stagingu s istom temom provjeriti da je jedan `og:title` s opisnim naslovom i jedan neprazan `og:description`.
- [ ] Provjeriti da su glavni HTML `<title>`, canonical i OG svih smještajnih stranica neizmijenjeni.
- [ ] Snimiti usporedbu izlaza prije/poslije.
- [ ] Tek nakon pregleda i odobrenja spojiti i objaviti; PR ostaje **DRAFT**.

## Pokretanje testa lokalno

```bash
php -l seo.php
php -l drustvene.php
php ai-lab/tests/test-og-home.php
```
