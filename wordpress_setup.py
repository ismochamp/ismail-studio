#!/usr/bin/env python3
"""Start a local-only WordPress installation with persistent Docker volumes."""
import pathlib,secrets,subprocess,time
root=pathlib.Path(__file__).resolve().parent
env=root/'.local.env'
if not env.exists():
 env.write_text('COMPOSE_PROJECT_NAME=ih-portfolio-07-'+secrets.token_hex(4)+'\nDB_PASSWORD='+secrets.token_hex(24)+'\nDB_ROOT_PASSWORD='+secrets.token_hex(24)+'\nWP_ADMIN_PASSWORD='+secrets.token_hex(18)+'\n');env.chmod(0o600)
cmd=['docker','compose','--env-file',str(env)]
subprocess.run(cmd+['up','-d'],cwd=root,check=True)
for attempt in range(30):
 result=subprocess.run(cmd+['exec','-T','wordpress','php','/tmp/portfolio-bootstrap.php'],cwd=root,capture_output=True,text=True)
 if result.returncode==0:
  print(result.stdout);break
 time.sleep(2)
else:
 print(result.stderr);raise SystemExit('WordPress initialization failed')
print('Open http://127.0.0.1:8187 | Admin: /wp-admin/ | User: portfolio_admin | Password: WP_ADMIN_PASSWORD in .local.env')
