# SEO i AI plan: apartmanibihac.com

Plan se oslanja na nalaze iz `SEO-AUDIT.md`. Prioriteti vrijede za stanje 11. 11. 2026.

Načela su ista za sve stavke: male izmjene koje se mogu vratiti, URL-ovi ostaju, dizajn ostaje, ništa se ne izmišlja.

## P0: odmah

| # | Što | Korist | Rizik | Tko | Stanje |
|---|---|---|---|---|---|
| 0.1 | **Sigurnosna kopija** prije objave: hPanel → Backups (datoteke i baza) | mogućnost povratka | — | vlasnik | čeka |
| 0.2 | **Pretraga s naslovnice** i 404 na `/oprema/` (nalazi 1–2) | gosti dobiju filtrirane rezultate; sitemap bez 404 | nizak: osvježavanje pravila adresa, kao pri svakoj nadogradnji | kod | ✅ pripremljeno |
| 0.3 | **Bingbot 403 / GPTBot 429** (nalaz 11) | Bing, Copilot i dio ChatGPT pretrage vide web | — | vlasnik: Bing WMT *Live URL*; hPanel → Security → bot / AI crawler zaštita | čeka provjeru |
| 0.4 | **Spojiti PR #3** (OG naslov i opis naslovnice) | ispravan prikaz pri dijeljenju na Facebook/WhatsApp; nestaje „Početna“ | nizak | pregled vlasnika | čeka spajanje |
| 0.5 | Dvostruki meta opis (nalaz 3), hreflang (4), dupli H1 (5), gramatika (6), EN/DE kartice (7), linkovi preko 301 (8), llms.txt (9), BlogPosting (10) | čišći signali Googleu; bolji prikaz na EN/DE | nizak | kod | ✅ pripremljeno |
| 0.6 | Odluke vlasnika: `/o-nama/` (Optika), članak iz 2024., jedna formulacija o avansu/kapari | povjerenje, točnost za AI odgovore | — | vlasnik | čeka |

**Redoslijed objave (nakon odobrenja):**
1. Backup.
2. Dodatak s ove grane (`wp-content/plugins/apartmani-upiti/`).
3. Tema 1.1.8: `git apply zakrpe/tema-1.1.8.patch` na grani `tema`, pa `wp-content/themes/apartmanibihac/`.
4. Provjere iz `SEO-CHANGES.md`.
5. U Search Consoleu i Bing WMT ponovno poslati `wp-sitemap.xml`.

Dodatak i tema ne ovise jedno o drugom i mogu se objaviti odvojeno.

## P1: sljedeća 2–4 tjedna

1. **Mjerenje (preduvjet za sve ostalo).** Na webu nema analitike.
   - Predlog: **GA4** s Consent Mode v2 i bannerom za pristanak, ili analitika bez kolačića (Plausible / Matomo bez kolačića).
   - Događaji: `klik_telefon`, `klik_whatsapp`, `klik_viber`, `upit_poslan` (uspješno slanje `abu-form`), `teambuilding_upit`.
   - Upit i potvrđena rezervacija su različite stvari: potvrde se već bilježe u tablici `abu_upiti` (status `potvrden` / `placeno`). Za usporedbu je dovoljno mjesečno izvesti broj upita i potvrda po objektu.
   - AI promet: u GA4 izraditi kanal za izvore `chatgpt.com`, `chat.openai.com`, `perplexity.ai`, `copilot.microsoft.com`, `bing.com/chat`, `gemini.google.com` i `claude.ai`.
2. **Search Console i Bing Webmaster Tools.**
   - Potvrditi vlasništvo i poslati sitemap.
   - U GSC provjeriti *Settings → Search generative AI* (je li sadržaj uključen u AI rezultate).
   - U Bing WMT otvoriti *AI Performance*.
   - Izvući početno stanje: upiti, klikovi, CTR, pozicija, države, uređaji (28 dana) → `seo-audit/gsc-pocetno.csv`.
3. **Jedan poslovni entitet u schemi.** Spojiti `TravelAgency` (tema) i `LodgingBusiness` (dodatak, naslovnica) pod `@id …/#org` s jednim imenom. Prijedlog je „Apartmani & Vikendice Bihać“, kao na Facebooku. Vlasnik potvrđuje ime.
4. ✅ **Tematsko povezivanje** (nalaz 8), pripremljeno u `povezivanje.php`; samo gdje veza stvarno postoji:
   - članak o Štrbačkom buku i NP Una → objekti u Lohovu (smjer parka) i stranica s pitanjima *Štrbački buk*;
   - rafting članci → Grmuša (Green River, Vikendica Unadom) i `/rafting-na-uni/`;
   - Plitvice → apartmani u centru i *Plitvice iz Bihaća* (pitanja);
   - stranice s pitanjima → blok „Smještaj u blizini“ s 3 objekta prema temi.

   Izvedba: mapa tema → objekti u kodu, dodaje se na kraj sadržaja, sadržaj u bazi se ne dira.
5. **Naslovi dulji od 70 znakova (44).** Kad naslov već sadrži „Bihać“, ne dodavati „– Apartmani i vikendice Bihać“ (filter `document_title_parts`). Prije toga zabilježiti CTR u GSC, pa usporediti nakon 4 tjedna.
6. **Page cache.** Uključiti LiteSpeed Cache (Hostinger ga podržava) samo za neprijavljene posjetitelje.
   - Isključiti iz cachea: `/smjestaj/` s parametrima, `admin-post.php`, stranice s `abu_a=`.
   - Prije uključivanja testirati kalendar i nonce u formi upita.
   - Cilj je TTFB ispod 0,3 s.
7. **Terenski CWV** iz GSC: LCP ≤ 2,5 s, INP ≤ 200 ms, CLS ≤ 0,1, posebno za mobilne uređaje.
8. **Alt tekst** za logotipe partnera na team building stranicama (33–36 po jeziku): naziv partnera, iz `sadrzaj/teambuilding.json`.

## P2: nakon što postoje podaci

1. **Stranice objekata na EN/DE:** prijevod uvoda i opreme za 19 objekata kao zasebni URL-ovi pod `/en/` i `/de/` s hreflang parovima. Raditi samo ako GSC pokaže potražnju iz DE/AT/CH i EN tržišta.
2. **Pregled 1.000 pitanja po podacima:** zatvoriti `pitanja/ZA-PROVJERU.md`. Ako tema nakon 90 dana nema impresija ni klikova, spojiti je ili prepisati. Bez masovnog `noindex`.
3. **Gap analiza ključnih riječi:**
   - Postoje: smještaj, apartmani, jeftin smještaj, vikendice na Uni, sobe, lokacije (Lohovo, Pritoka, Grmuša), rafting, team building, Plitvice, s djecom, vikend.
   - Kandidati za provjeru u GSC: „stan na dan Bihać“ (bez stranice; moguće je pokriti postojećom `/jeftin-smjestaj-bihac/`), „kuće za odmor Bihać“, „smještaj Nacionalni park Una“, „Štrbački buk smještaj“.
   - Novu stranicu raditi samo ako postojeća ne može odgovoriti na upit.
4. **Vanjska vidljivost:**
   - Turistička zajednica USK / Grad Bihać (popis smještaja), NP Una (smještaj u okolici), Una Olympic Rafting (partnerski link), lokalni portali.
   - Bez kupljenih linkova.
   - Google Business Profile samo za stvarnu adresu s osobljem koja ispunjava pravila; ne otvarati zaseban profil za svaku kuću.
5. **robots.txt:** u grupe AI botova dodati iste `Disallow` kao za `*`. Kozmetička izmjena.
6. **Oznaka izvora u traci:** „9.362 recenzije **na Facebooku**“. Tekstualna izmjena; potrebna odluka vlasnika.

## Što se ne radi

- Ne radi se novi SEO dodatak (Yoast / Rank Math). Postojeći kod već pokriva naslove i opise, a dodatak bi ponovno udvostručio meta oznake.
- Ne radi se masovno generiranje novih članaka ili pitanja.
- Ne dodaju se lažni `AggregateRating` ni recenzije, i ne obećava se pojavljivanje u AI odgovorima.
- IndexNow se ne predstavlja kao rješenje za Google. Google ga ne koristi; za Google vrijede sitemap i Search Console.

## Praćenje (30 / 60 / 90 dana od objave)

| Metrika | Izvor | Početno |
|---|---|---|
| Indeksirane stranice, greške 404 | GSC → Indeksiranje | izvući |
| Klikovi, impresije, CTR (brand / nebrand, po jeziku) | GSC | izvući |
| Citiranja u Copilotu | Bing WMT → AI Performance | izvući |
| AI vidljivost (32 fiksna upita) | `ai-lab/visibility` (PR #4) | izvući ručno |
| Klikovi telefon / WhatsApp / Viber | GA4 (nakon P1.1) | 0 (ne mjeri se) |
| Poslani upiti, potvrđene rezervacije | tablica `abu_upiti` | izvući |
