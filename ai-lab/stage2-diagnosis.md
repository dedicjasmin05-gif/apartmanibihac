# Korak 2 — provjera aktivnog SEO naslova i rezervacijskog obrasca

**Datum:** 9. studenoga 2026. — čitanje javnog HTML-a i postojećeg GitHub koda, bez promjena produkcije.

## Potvrđeno

1. **HTML `<title>` početne radi:** `Smještaj Bihać: apartmani i vikendice na Uni od 25 €`. Raniji ekstrakcijski rezultat `Početna` odnosi se na `og:title`, ne na HTML title. **Ne mijenjati SEO title.**
2. **OG naslov/opis početne:** `og:title = Početna`, `og:description = ""`. Stvarna neusklađenost, provjeriti temu.
3. **Duplicirani meta description:** na početnoj stranici postoje dva `<meta name="description">` s različitim tekstovima, a na Apartman Centar dva istog sadržaja. Sačuvati provjereni postojeći opis; prvo pronaći dva generatora.
4. **Canonical postoji:** na početnoj `https://apartmanibihac.com/`, na objektu `https://apartmanibihac.com/smjestaj/apartman-centar/`. Nema razloga duplicirati canonical.
5. **Aktivna tema:** iz javnog CSS URL-a vidi se slug `wp-content/themes/apartmanibihac/`, glavni stylesheet nosi parametar `ver=1.1.7`. To ne dokazuje internu verziju teme. Izvorni kod teme nije dio pregledanog GitHub repo-a.
6. **Booking:** javni HTML `form.abu-form` na Apartman Centar ima `method="post"` i eksplicitni `action="https://apartmanibihac.com/wp-admin/admin-post.php"`, obvezne podatke i WordPress nonce. Kalendar i obrazac već se prikazuju. **Nisu poslani stvarni upiti.**
7. **Postojeći kod:** `seo.php` registrira naslovni filter, a `apartmani-upiti.php` registrira oba booking shortcoda i handler. Nema potrebe uvoditi novi SEO/booking plugin.

## Što nam još nedostaje

- Provjeriti u stvarnoj temi gdje se generiraju OG meta tagovi i drugi meta description element. To ne možemo pouzdano utvrditi iz javnog HTML-a.
- Provjeriti je li neki SEO dodatak aktivan. Source kod ima guard za Yoast/RankMath/AIOSEO, ali ispravan HTML title znači da ovo **nije potvrđen kvar**.
- Na izoliranom stagingu provjeriti prijenos, e-mail i ical bez lažnih rezervacija na produkciji.

## Kandidat za najmanju buduću doradu

- Samo za **početnu** dati opisni OG title i nonempty OG description, koristeći potvrđeni tekst postojeće početne, uz zaštitu od dupliciranja.
- Ukloniti izvor duplog meta description nakon identifikacije u temi/SEO komponenti.
- Ne mijenjati HTML title, katalog objekata, rezervacijski sustav ni aktivne podatke bez testiranja.

## Novi lokalni alati (samo audit)

```bash
python3 ai-lab/check_existing_contracts.py
python3 -m unittest discover -s ai-lab -p 'test_*.py' -v
```

Skripte ne izvršavaju WordPress i ne šalju upite/rezervacije. **Nema mergea, deploya ni kupnji.**
