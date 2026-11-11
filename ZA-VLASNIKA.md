# Što je na vama: redom

Kod je gotov i čeka na grani `ccr-ab0db569-mim4o1`. Opis svake izmjene je u `SEO-CHANGES.md`.

## 1. Hitno: vidi li Bing web? (10 min)
1. Otvorite **Bing Webmaster Tools** → *URL Inspection* → upišite `https://apartmanibihac.com/` → **Live URL**.
2. Ako Bing javi blokadu ili 403, otvorite **hPanel** → web *apartmanibihac.com* → *Security*. Isključite blokadu AI crawlera ili botova, ili dodajte Bingbot i OAI-SearchBot kao dopuštene.
3. Ako web još nije dodan u Bing Webmaster Tools: *Add site* → *Import from Google Search Console* (najbrže).

## 2. Prije objave (5 min)
1. **Sigurnosna kopija:** hPanel → *Backups* → nova kopija datoteka i baze.
2. **Search Console** → *Performance* → razdoblje 3 mjeseca → *Export*. Pošaljite mi datoteku; to je početno stanje za usporedbu.

## 3. Odobrite objavu
Recite „objavi“. Tada:
- učitavam dodatak i temu 1.1.8;
- provjeravam svih 15 točaka „prije/poslije“, posebno da pretraga s naslovnice opet filtrira;
- šaljem vam rezultat.

Uz to spojite i **PR #3** na GitHubu (OG naslov naslovnice), ili mi recite da ga spojim ja.

## 4. Odluke o sadržaju (odgovorite jednom porukom)
1. **`/o-nama/`** sada govori o Optici Dervišević. Napišite 3–4 rečenice o sebi (tko ste, od kada iznajmljujete, s koliko vlasnika radite) i ja ću složiti stranicu.
2. **Članak iz 2024.** o hostelima: obrisati, sakriti od Googlea ili prepisati?
3. **Plaćanje:** koja je istina?
   - (a) „Plaća se gotovinom pri dolasku; avans samo kod nekih objekata“
   - (b) „Avans je dobrovoljan i daje popust“
   - (c) „Kapara 30 % s rokom“

   Uskladit ću to na webu, u pitanjima, u llms.txt i u e-mailovima.
4. **Ljubimci:** koji objekti primaju pse? Dodat ću ih na stranice o putovanju sa psom.
5. **Traka „9.362 recenzije“:** smijem li dodati „na Facebooku“, da bude jasno odakle su recenzije?

## 5. Nakon objave
1. **Search Console** → *Sitemaps* → ponovno pošaljite `wp-sitemap.xml`.
2. **Search Console** → *Settings* → *Search generative AI*: provjerite je li uključeno.
3. **Bing Webmaster Tools** → *Sitemaps* → isto; zatim *AI Performance*.
4. Za 30, 60 i 90 dana: u adminu otvorite **Upiti → Mjerenje** i pošaljite mi snimku zaslona, zajedno s izvozom iz Search Consolea. Usporedit ću s početnim stanjem.
