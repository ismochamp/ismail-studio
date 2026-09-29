"""Integration checks against the local WordPress installation; removes its fixture."""
import http.cookiejar,re,html
from pathlib import Path
from urllib.request import build_opener,HTTPCookieProcessor,Request
from urllib.error import HTTPError
from urllib.parse import urlencode
base='http://127.0.0.1:8187';web=build_opener(HTTPCookieProcessor(http.cookiejar.CookieJar()))
def get(p):return web.open(base+p).read().decode()
def post(p,data):return web.open(Request(base+p,data=urlencode(data).encode())).read().decode()
def value(text,name):return re.search(r'name="'+name+r'" value="([^"]+)"',text).group(1)
assert 'Less friction.' in get('/');print('PASS: active theme homepage')
form=get('/contact/');nonce=value(form,'ih_nonce');assert 'ih_submit' in form;print('PASS: contact shortcode and nonce')
try:post('/wp-admin/admin-post.php',{'action':'ih_submit','name':'TEST FIXTURE'})
except HTTPError as e:assert e.code==403;e.close()
else:raise AssertionError('Missing nonce accepted')
print('PASS: missing nonce rejected')
data=dict(action='ih_submit',ih_nonce=nonce,name='TEST FIXTURE — inquiry integration check',email='test@example.invalid',service='integration',message='TEST FIXTURE: Verify WordPress inquiry persistence and review.',consent='yes')
assert 'Your inquiry is saved' in post('/wp-admin/admin-post.php',data);print('PASS: valid form persisted and confirmation shown')
password=dict(line.split('=',1) for line in (Path(__file__).parent/'.local.env').read_text().splitlines())['WP_ADMIN_PASSWORD']
get('/wp-login.php');post('/wp-login.php',{'log':'portfolio_admin','pwd':password,'wp-submit':'Log In','redirect_to':base+'/wp-admin/','testcookie':'1'})
inbox=get('/wp-admin/admin.php?page=ih-inquiries');assert data['name'] in inbox;print('PASS: authenticated staff inbox shows persisted inquiry')
record=re.search(r'<div style="background:white[^>]*>.*?TEST FIXTURE.*?</form></div>',inbox,re.S).group(0)
ident=value(record,'id');update_nonce=value(record,'_wpnonce')
reviewed=post('/wp-admin/admin-post.php',dict(action='ih_update',id=ident,_wpnonce=update_nonce,operation='reviewed'));assert 'reviewed' in reviewed;print('PASS: staff review state updated')
export_url=html.unescape(re.search(r'href="([^"]*action=ih_export[^"]+)"',inbox).group(1))
export=web.open(export_url).read().decode();assert data['email'] in export;print('PASS: authenticated CSV export')
post('/wp-admin/admin-post.php',dict(action='ih_update',id=ident,_wpnonce=update_nonce,operation='delete'))
for block in re.findall(r'<div style="background:white[^>]*>.*?</form></div>',get('/wp-admin/admin.php?page=ih-inquiries'),re.S):
 if data['name'] in block:post('/wp-admin/admin-post.php',dict(action='ih_update',id=value(block,'id'),_wpnonce=value(block,'_wpnonce'),operation='delete'))
assert data['name'] not in get('/wp-admin/admin.php?page=ih-inquiries');print('PASS: test inquiries deleted')
print('8 WordPress integration checks passed; no test inquiries retained.')
