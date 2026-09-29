import http.cookiejar, os, re, socket, sqlite3, subprocess, sys, tempfile, time, unittest
from pathlib import Path
from urllib.request import build_opener, HTTPCookieProcessor, Request
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode
ROOT=Path(__file__).resolve().parent
class ServiceTests(unittest.TestCase):
 @classmethod
 def setUpClass(c):
  c.temp=tempfile.TemporaryDirectory();c.db=Path(c.temp.name)/'test.sqlite3'
  with socket.socket() as s:s.bind(('127.0.0.1',0));c.port=s.getsockname()[1]
  c.proc=subprocess.Popen([sys.executable,str(ROOT/'app.py')],env={**os.environ,'PORT':str(c.port),'PORTFOLIO_DB':str(c.db)},stdout=subprocess.DEVNULL)
  c.base=f'http://127.0.0.1:{c.port}'
  for i in range(50):
   try:build_opener().open(c.base+'/health',timeout=1);break
   except URLError:time.sleep(.05)
 @classmethod
 def tearDownClass(c):c.proc.terminate();c.proc.wait();c.temp.cleanup()
 def setUp(self):self.web=build_opener(HTTPCookieProcessor(http.cookiejar.CookieJar()))
 def get(self,p):return self.web.open(self.base+p).read().decode()
 def form(self):return re.search(r'name="csrf" value="([^"]+)"',self.get('/contact')).group(1)
 def post(self,p,d):return self.web.open(Request(self.base+p,data=urlencode(d).encode())).read().decode()
 def test_pages_and_missing_page(self):
  for p in ['/','/services','/about','/contact','/privacy','/inbox']:self.assertIn('<html',self.get(p))
  with self.assertRaises(HTTPError) as x:self.get('/missing')
  self.assertEqual(x.exception.code,404)
 def test_csrf_rejects_missing_token(self):
  with self.assertRaises(HTTPError) as x:self.post('/inquiries',{'name':'Test'})
  self.assertEqual(x.exception.code,403)
 def test_server_validation(self):
  with self.assertRaises(HTTPError) as x:self.post('/inquiries',{'csrf':self.form(),'name':'Test','email':'invalid','message':'short'})
  self.assertEqual(x.exception.code,400)
 def test_inquiry_storage_escape_export_review_delete(self):
  token=self.form();name='=TEST FIXTURE <script>alert(1)</script>';message='TEST FIXTURE: Check an actual integration workflow.'
  self.assertIn('inquiry is saved',self.post('/inquiries',dict(csrf=token,name=name,email='test@example.invalid',service='integration',message=message,consent='yes')))
  with sqlite3.connect(self.db) as c:row=c.execute('SELECT id,name,message FROM inquiries WHERE name=?',(name,)).fetchone()
  self.assertEqual(row[1:],(name,message));inbox=self.get('/inbox');self.assertIn('&lt;script&gt;',inbox);self.assertNotIn('<script>alert',inbox)
  self.assertIn("'=TEST FIXTURE",self.get('/export'))
  self.assertIn('reviewed',self.post('/inbox/action',dict(csrf=token,id=row[0],action='reviewed')))
  self.post('/inbox/action',dict(csrf=token,id=row[0],action='delete'))
  with sqlite3.connect(self.db) as c:self.assertEqual(c.execute('SELECT count(*) FROM inquiries WHERE id=?',(row[0],)).fetchone()[0],0)
if __name__=='__main__':unittest.main(verbosity=2)
