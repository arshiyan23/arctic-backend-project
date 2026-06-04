#!/bin/bash
CFG=/etc/nginx/sites-enabled/default

# Update doc root and script paths for web/ subdir
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/web;|' $CFG
sed -i 's|/home/site/wwwroot/index|/home/site/wwwroot/web/index|' $CFG
sed -i 's|/home/site/wwwroot\$fastcgi_script_name|/home/site/wwwroot/web\$fastcgi_script_name|' $CFG

# Remove all if-block security rules that block brackets in query strings
python3 << 'PYEOF'
import re
with open('/etc/nginx/sites-enabled/default') as f:
    c = f.read()

# Remove if ($args ~ ... ) { ... } blocks (including multiline)
c = re.sub(r'\s*if\s*\(\s*\$args\s*~.*?\{[^}]*\}', '', c, flags=re.DOTALL)
# Remove if ($request_uri ~ ... ) { ... } blocks
c = re.sub(r'\s*if\s*\(\s*\$request_uri\s*~.*?\{[^}]*\}', '', c, flags=re.DOTALL)

with open('/etc/nginx/sites-enabled/default', 'w') as f:
    f.write(c)
PYEOF

exit 0
