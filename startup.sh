#!/bin/bash
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/web;|' /etc/nginx/sites-enabled/default
sed -i 's|/home/site/wwwroot/index|/home/site/wwwroot/web/index|' /etc/nginx/sites-enabled/default
sed -i 's|/home/site/wwwroot\$fastcgi_script_name|/home/site/wwwroot/web\$fastcgi_script_name|' /etc/nginx/sites-enabled/default
sed -i 's|try_files \$uri \$uri/ =404;|try_files \$uri /index.php?\$query_string;|' /etc/nginx/sites-enabled/default
exit 0
