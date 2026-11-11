# Read-only inventura svih URL-ova iz wp-sitemap.xml (jedan GET po URL-u, 0,3 s razmaka). Pokretanje: python3 seo-audit/crawl.py
import re, json, sys, time, urllib.request, html
from urllib.parse import urljoin, urlparse
UA='Mozilla/5.0 (compatible; seo-audit)'
def get(u, ua=UA):
    r=urllib.request.Request(u, headers={'User-Agent':ua})
    class NR(urllib.request.HTTPRedirectHandler):
        def redirect_request(self,*a,**k): return None
    op=urllib.request.build_opener(NR)
    t=time.time()
    try:
        resp=op.open(r, timeout=30); code=resp.status; body=resp.read().decode('utf-8','replace'); loc=''
    except urllib.error.HTTPError as e:
        code=e.code; body=e.read().decode('utf-8','replace'); loc=e.headers.get('Location','')
    return code, body, loc, round(time.time()-t,2)
sm=[]
_,b,_,_=get('https://apartmanibihac.com/wp-sitemap.xml')
for s in re.findall(r'<loc>([^<]+)',b):
    _,sb,_,_=get(s)
    for u in re.findall(r'<loc>([^<]+)',sb): sm.append((s.split('sitemap-')[1].replace('-1.xml',''),u))
print(len(sm),'URL u sitemapu',file=sys.stderr)
out=[]
for typ,u in sm:
    code,b,loc,t=get(u)
    head=b.split('</head>')[0]
    f=lambda p: [html.unescape(x) for x in re.findall(p,head,re.I|re.S)]
    links=set()
    for h in re.findall(r'<a\s[^>]*href="([^"#]+)',b):
        a=urljoin(u,h); p=urlparse(a)
        if p.netloc=='apartmanibihac.com': links.add(p.scheme+'://'+p.netloc+p.path)
    main=b.split('<main',1)[-1].split('</main>')[0]
    txt=re.sub(r'<script.*?</script>|<style.*?</style>','',main,flags=re.S); txt=re.sub(r'<[^>]+>',' ',txt)
    out.append(dict(typ=typ,url=u,code=code,loc=loc,t=t,kb=len(b)>>10,
      title=f(r'<title>(.*?)</title>'),desc=f(r'<meta name="description" content="([^"]*)"'),
      ogt=f(r'property="og:title" content="([^"]*)"'),ogd=f(r'property="og:description" content="([^"]*)"'),ogi=f(r'property="og:image" content="([^"]*)"'),
      canon=f(r'rel=["\']canonical["\'][^>]*href=["\']([^"\']+)'),robots=f(r"name=['\"]robots['\"] content=['\"]([^'\"]+)"),
      lang=re.findall(r'<html lang="([^"]+)"',b),hreflang=re.findall(r'hreflang="([^"]+)" href="([^"]+)"',head),
      ld=re.findall(r'"@type":"([A-Za-z]+)"',' '.join(re.findall(r'application/ld\+json">(.*?)</script>',b,re.S))),
      h1=[re.sub(r'<[^>]+>','',x).strip() for x in re.findall(r'<h1[^>]*>(.*?)</h1>',b,re.S)],
      words=len(txt.split()),imgs=len(re.findall(r'<img ',b)),noalt=len(re.findall(r'<img (?![^>]*alt="[^"]+")[^>]*>',b)),
      links=sorted(links)))
    time.sleep(0.3)
json.dump(out,open('crawl.json','w'),ensure_ascii=False,indent=0)
