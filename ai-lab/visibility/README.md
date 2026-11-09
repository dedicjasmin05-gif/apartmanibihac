# AI Search Lab — etapni razvoj: monitor preporuka

**Cilj:** dokazivo mjeriti pojavljuje li se apartmanibihac.com u AI odgovorima. Ovaj modul **ne mijenja WordPress, ne pretražuje automatski AI platforme, ne koristi plaćene API-je i ne šalje rezervacijske upite**.

## Što postoji

- `prompts.json`: 32 unaprijed zaključana upita, osam tema × bosanski/hrvatski/engleski/njemački.
- `monitor.py`: lokalni upis opažanja uz obaveznu bilješku o dokazu, lokalni CSV izvještaj i prebrojavanje rezultata po platformi/jeziku/tržištu.
- `test_monitor.py`: osam offline regresijskih testova.
- `baseline-google-ai-mode-2026-11-09.md`: korisnikov prvi primjer, jasno odvojen od zaključka o ukupnoj vidljivosti.

## Pokretanje, bez dodatnih instalacija

Zahtijeva Python 3.9+. Iz korijena repozitorija:

```bash
python3 ai-lab/visibility/monitor.py list
python3 -m unittest discover -s ai-lab/visibility -p 'test_*.py' -v
```

Nakon **stvarno provjerenog** odgovora na točno jedan fiksni upit, ručno zabilježi nalaz:

```bash
python3 ai-lab/visibility/monitor.py record \
  --platform google-ai \
  --query-id general-bs \
  --market Croatia \
  --mentioned no \
  --cited no \
  --competitors 'booking.com;agoda.com' \
  --evidence 'Screenshot 2026-11-09; samo vidljivi dio odgovora'
python3 ai-lab/visibility/monitor.py report
```

To je primjer unosa; **ne pokretati automatski** na osnovi simulacije. Pojedini rezultat može varirati po lokaciji, jeziku, vremenu, personalizaciji i korištenoj verziji modela.

Sprema se samo lokalni `observations.local.csv` (ne treba ga commitati). Bilješka o dokazu je obavezna i kratka; ne kopirati privatne podatke, pune odgovore niti adrese rezervacija.

## Obvezna interpretacija

- `mentioned=yes`: naziv apartmanibihac.com ili brenda izričito spomenut u odgovoru.
- `cited=yes`: odgovor izričito navodi link na našu domenu kao izvor.
- Ta dva svojstva su neovisna: **sustav može citirati URL bez spominjanja brenda**.
- `unclear`: ne može se utvrditi zbog djelomičnog screenshota ili skrivene liste izvora.
- Zbroj opažanja nije „AI rang”, tržišni udio ni dokaz uzročnosti neke SEO izmjene.
- Prije/poslije usporedba valjana je tek kad ostanu isti upiti, jezik, tržište, platforma i pravila bilježenja. Koristiti više ponavljanja i navesti datum.

## Granice

Nema web scraping automatizacije Google AI Modea, ChatGPT-a ni drugih platformi, nema botova koji klikaju, nema zaobilaženja ograničenja i nema automatskog generiranja tvrdnji o pozicijama.

Sljedeća faza nakon realnog početnog uzorka: dashboard koji iz ovih lokalnih mjerenja pokazuje trendove, konkurentske domene i citirane podstranice. Tek potom procijeniti isplativost službenih plaćenih API-jeva.

**Ne spajati niti objavljivati bez pregleda.**
