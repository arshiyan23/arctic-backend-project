#!/bin/bash
CFG=/etc/nginx/sites-enabled/default

# Change doc root to web/
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/web;|' $CFG

# Add JSON:API bypass location BEFORE the PHP regex location block
# Using ^~ prefix makes nginx skip regex checks (which contain security rules)
# Insert before the closing brace of the server block
head -n -1 $CFG > /tmp/nginx_updated
cat >> /tmp/nginx_updated << 'LOCEOF'
  location ^~ /index.php/jsonapi {
    include fastcgi_params;
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_param SCRIPT_FILENAME /home/site/wwwroot/web/index.php;
    fastcgi_param REQUEST_URI $request_uri;
    fastcgi_split_path_info ^(/index\.php)(/.*)$;
    fastcgi_param PATH_INFO $fastcgi_path_info;
    fastcgi_param SCRIPT_NAME /index.php;
  }
LOCEOF
echo "}" >> /tmp/nginx_updated
cp /tmp/nginx_updated $CFG

# Verify config is valid
nginx -t

exit 0
