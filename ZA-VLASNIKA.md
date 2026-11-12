# Što je na vama: redom

Kod je gotov i čeka na grani `ccr-ab0db569-mim4o1`. Opis svake izmjene je u `SEO-CHANGES.md`.

## 1. Hitno: vidi li Bing web? (10 min)
1. Otvorite **Bing Webmaster Tools** → *URL Inspection* → upišite `https://apartmanibihac.com/` → **Live URL**.
2. Ako Bing javi blokadu ili 403, otvorite **hPanel** → web *apartmanibihac.com* → *Security*. Isključite blokadu AI crawlera ili botova, ili dodajte Bingbot i OAI-SearchBot kao dopuštene.
3. Ako web još nije dodan u Bing Webmaster Tools: *Add site* → *Import from Google Search Console* (najbrže).

## 2–4. Objava i odluke: gotovo (11. 11. 2026.)
Objavljeni su dodatak 1.1.4 i tema 1.1.8, spojen je PR #3, napravljene su stranica O nama, jedno pravilo plaćanja i ljubimci na upit, članak iz 2024. je sakriven od Googlea, a uz recenzije stoji „na Facebooku“. Provjera: 20/20 (`seo-audit/provjera-poslije-2026-11-11.txt`).

## 5. Nakon objave
1. **Search Console** → *Sitemaps* → ponovno pošaljite `wp-sitemap.xml`.
2. **Search Console** → *Settings* → *Search generative AI*: provjerite je li uključeno.
3. **Bing Webmaster Tools** → *Sitemaps* → isto; zatim *AI Performance*.
4. Za 30, 60 i 90 dana: u adminu otvorite **Upiti → Mjerenje** i pošaljite mi snimku zaslona, zajedno s izvozom iz Search Consolea. Usporedit ću s početnim stanjem.

## Naknadno (kad stignete)
- **iCal linkovi** s Bookinga i Airbnba za objekte koji su ondje: bez njih kalendar na webu ne zna za tamošnje rezervacije.
- **Fotografije** za Japodsku kućicu na drvetu i Villu Mekić.
- Upiti zasad stižu na vaš e-mail. Kad upit 3 sata čeka odgovor, stiže podsjetnik, a nakon 12 sati poruka **„HITNO: upit istekao“** s kontaktom gosta.


## Facebook i Instagram: automatsko objavljivanje (jednom, oko 15 minuta, s računala)
Modul je na webu: **WP admin → Upiti → Društvene mreže**. Dok nema tokena, tamo su gotovi tekstovi za ručno kopiranje.

1. Instagram račun mora biti **profesionalni (Business)** i povezan s Facebook stranicom Apartmani & Vikendice (Meta Business Suite → Postavke → Povezani računi).
2. Otvorite https://developers.facebook.com/apps i napravite aplikaciju tipa **Business**.
3. Otvorite **Graph API Explorer** (https://developers.facebook.com/tools/explorer/) i odaberite tu aplikaciju. Pod *User or Page* odaberite stranicu **Apartmani & Vikendice**. Dodajte dozvole `pages_manage_posts`, `pages_read_engagement`, `instagram_basic` i `instagram_content_publish`, pa kliknite **Generate Access Token**.
4. Token produžite na trajni: **Access Token Debugger** → *Extend Access Token*. Zatim u Exploreru pokrenite `me/accounts` i kopirajte `access_token` i `id` stranice. Token stranice dobiven iz produženog tokena ne istječe.
5. U Exploreru pokrenite `<id stranice>?fields=instagram_business_account`. Broj koji dobijete je **Instagram Business ID**.
6. U WP adminu (Upiti → Društvene mreže) upišite ID stranice, token i Instagram ID, pa kliknite **Spremi**. Ako je sve ispravno, ispiše se ime stranice i broj pratitelja.
7. Uključite **Automatika**. Od tada svaki novi članak ide na Facebook i Instagram 10 minuta nakon objave. Starije članke možete objaviti gumbom **Objavi**.

Token se čuva samo u bazi weba, ne u kodu. Ne šaljite ga u chat.
