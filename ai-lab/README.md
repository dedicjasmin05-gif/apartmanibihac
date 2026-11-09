# AI Search Lab — korak 1 / read-only baseline

Prvi samostalni alat za mjerenje tehničke AI/SEO spremnosti apartmanibihac.com, **bez mijenjanja produkcije**. Dopunjuje postojeći `alati/seo-test.php` (ne zamjenjuje ga).

## Lokalno pokretanje
```bash
cd ai-lab
python3 -m unittest -v test_audit_readonly.py
python3 audit_readonly.py --output ai-lab-baseline.json
```

- Testovi su offline, bez mrežnih poziva.
- Audit koristi do 9 javnih HTTP GET zahtjeva na unaprijed ograničenim URL-ovima iste domene: 7 stranica, robots.txt i wp-sitemap.xml.
- Ne koristi WordPress pristup, lozinke, POST, API tokene, rezervacije, mailove ili plaćene servise; ne šalje upite vlasnicima apartmana.
- Zabranjena su preusmjeravanja izvan dozvoljene HTTPS domene.
- Izvještaj se sprema **samo lokalno** i ne sadrži vrijednosti obrazaca ni sadržaj upita gostiju.
- Flagovi su znak za *ručni pregled*, nisu AI-rangiranje ni dokaz da neki obrazac ne radi.

## Potvrđeno opažanje (9. 11. 2026.)
- Aktivna početna stranica kroz javni pregled daje title „Početna” i prazan OG description. U postojećem `seo.php` već se pokušava postaviti opisniji naslov. Treba pregledati izvornu stranicu i prioritet filtera u temi; **ne dodavati drugi SEO plugin**.
- WordPress sitemap radi, AI user-agenti su u robots.txt već obuhvaćeni. Ne treba dodavati duplicirani sitemap ili „AI SEO” dodatak.
- Kalendar i obrazac za upit postoje kao shortcodeovi `abu_kalendar` i `abu_upit`, ali njihov prikaz nije potvrđen na testiranoj javnoj stranici. Provjeriti WordPress template/shortcode prije bilo kakve dogradnje.
- Cijene pojedinačnih oglasa trenutno su navedene kao „cijena na upit”. Ne objavljivati procijenjene cijene, raspoloživost ili recenzije.
- Korisnik je u Google AI Mode postavio „smjestaj u bihacu” (9. 11. 2026. oko 05:20). Jedan odgovor citira Booking/Agoda i neke hotele, a nije citirao portal. To nije statistički dokaz o ukupnoj AI vidljivosti.

## Naredna etapa, izvan ovog PR-a
1. Utvrditi uzrok live generičkog naslovnog taga — minimalna izmjena tek na stagingu uz korisnikovo odobrenje.
2. Testirati interaktivni kalendar i obrazac bez stvarnog slanja rezervacija.
3. Zabilježiti AI vidljivost za fiksne upite (HR/BS/EN/DE), odvojiti Google AI Mode, ChatGPT, Bing; bez automatskog scrapeanja odgovora protiv pravila platforme.
4. Provjeriti Search Console / Bing Webmaster ako korisnik odobri pristup.

**Ovaj PR ostaje nacrt. Ne spajati u main niti postavljati na server bez pregleda.**
