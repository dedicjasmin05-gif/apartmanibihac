# Korak 2 — dijagnostika bez diranja postojećih funkcija

Datum: 9. studenoga 2026.  
Status: potvrđene informacije iz repozitorija + opažanja na javnoj stranici. **Nije proveden WordPress administratorski/server-side runtime pregled.**

## Što je potvrđeno

1. Postojeći \`seo.php\` registrira \`pre_get_document_title\` na prioritetu 20, i namjerava postaviti \`Smještaj Bihać: apartmani i vikendice na Uni od 25 €\` za početnu.
2. Taj isti filter **namjerno vraća izvorni naslov** ako je detektiran Yoast, Rank Math ili AIOSEO (\`abu_seo_plugin()\`). Aktivnost tih dodataka **nije potvrđena**. Drugim riječima, moguć je konflikt/izvor konfiguracije, ali uzrok još nije utvrđen.
3. \`apartmani-upiti.php\` već registrira \`[abu_kalendar]\` i \`[abu_upit]\`. Forma u kodu ima eksplicitni \`admin-post.php\`, nonce, validaciju datuma/gostiju i zaštitu od jednostavnog spama.
4. \`sadrzaj-objekti.php\` generira objašnjenje „Odaberite datume u kalendaru”, ali sam taj tekst ne ubacuje shortcode. Kalendar se ipak pojavio u javnom pregledniku testiranog Apartmana Centar, što upućuje na umetanje kroz aktivnu temu/predložak ili drugi sloj.
5. \`alati/seo-test.php\` je već postojeći alat za SEO pregled. Novi \`ai-lab/check_existing_contracts.py\` samo provjerava prisutnost osnovnih ugovora u izvornom kodu; ne pokreće PHP i ne komunicira sa serverom.

## Što je neizvjesno, i zašto ne mijenjamo produkciju

- Ekstrakcija metapodataka s javne stranice dala je naslov početne **„Početna”**, dok je browser pregled kod pojedinačnog objekta dao opisniji naslov. Potrebna je provjera stvarnog HTML sourcea i utjecaja cachea, teme i aktivnih SEO dodataka.
- Browser pregled pronašao je \`.abu-form\` i \`.abu-kal\` na Apartman Centar, ali nije pouzdano potvrdio HTML atribut \`action\` obrasca; kod ga eksplicitno postavlja. **To nije dokaz da je forma neispravna.** Nisu slani probni upiti.
- Različiti alati različito ekstraktiraju canonical i OG metapodatke. Ne dodavati drugi set metapodataka prije provjere izvornog HTML-a.

## Sljedeća najkraća provjera, bez izmjena

1. Na odobrenom serveru/hPanelu očitati aktivne SEO dodatke i naziv aktivne teme (read-only).
2. Usporediti izvorni HTML home + 1 objekt s public URL-a (idealno anonimno i uz cache bypass), posebno \`title\`, canonical, OG i \`form action\`.
3. Ako aktivan SEO dodatak: pregledati njegov naslov početne i njegove postavke; **ne nadjačavati filter** naslijepo.
4. Ako nije aktivan: pogledati theme filtere \`pre_get_document_title\`, \`document_title_parts\`, \`wp_title\` ili ručni \`<title>\`, a zatim pripremiti minimalnu ispravku na zasebnoj grani i testirati.
5. Na stagingu ispitati formu i calendar UI bez slanja pravog upita vlasnicima; tek uz odobrenje provesti kontrolirani end-to-end test slanja.

## Kako pokrenuti lokalni source audit

\`\`\`bash
python3 ai-lab/check_existing_contracts.py
python3 -m unittest discover -s ai-lab -p "test_*.py" -v
\`\`\`

Ovaj source audit ne čita lozinke, ne poziva API, ne piše u bazu i ne pokreće postojeće WordPress migracije.

**Nema deploymenta, mergea, kupnji, izmjena rezervacija niti izmjena cijena.**
