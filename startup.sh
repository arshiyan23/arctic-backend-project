#!/bin/bash

rm -rf /etc/nginx/security.d/
rm -rf /etc/nginx/modsecurity/
rm -rf /etc/modsecurity/
rm -f /etc/nginx/conf.d/security*
rm -f /etc/nginx/conf.d/modsecurity*
rm -f /etc/nginx/conf.d/waf*
rm -f /etc/nginx/conf.d/az-*
rm -f /etc/nginx/conf.d/oryx-*
rm -f /etc/nginx/snippets/security*

cat > /etc/nginx/sites-enabled/default << 'NGINXCFG'
server {
    listen 8080;
    server_name "";
    root /home/site/wwwroot/web;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
    }
}
NGINXCFG
