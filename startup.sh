#!/bin/bash
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/web;|' /etc/nginx/sites-enabled/default
sed -i 's|/home/site/wwwroot/index|/home/site/wwwroot/web/index|' /etc/nginx/sites-enabled/default
sed -i 's|/home/site/wwwroot\$fastcgi_script_name|/home/site/wwwroot/web\$fastcgi_script_name|' /etc/nginx/sites-enabled/default
sed -i 's|try_files \$uri \$uri/ =404;|try_files \$uri \$uri/ /index.php\$uri\$is_args\$args;|' /etc/nginx/sites-enabled/default
sed -i 's|try_files \$uri /index.php?\$query_string;|try_files \$uri \$uri/ /index.php\$uri\$is_args\$args;|' /etc/nginx/sites-enabled/default
grep -q 'try_files \$uri \$uri/ /index.php\$uri\$is_args\$args;' /etc/nginx/sites-enabled/default || \
  sed -i '/location \/ {/a\        try_files $uri $uri/ /index.php$uri$is_args$args;' /etc/nginx/sites-enabled/default
grep -q 'location \^~ /jsonapi/' /etc/nginx/sites-enabled/default || \
  sed -i '/location \/ {/i\    location = /jsonapi { rewrite ^ /index.php$request_uri last; }\n    location ^~ /jsonapi/ { rewrite ^ /index.php$request_uri last; }\n    location ^~ /subrequests { rewrite ^ /index.php$request_uri last; }\n    location ^~ /sites/default/files/styles/ { try_files $uri /index.php$uri$is_args$args; }\n' /etc/nginx/sites-enabled/default
exit 0
