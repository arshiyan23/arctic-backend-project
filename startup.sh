#!/bin/bash

# Remove ALL security nginx config files
rm -rf /etc/nginx/security.d/
rm -rf /etc/nginx/modsecurity/
rm -rf /etc/modsecurity/
rm -f /etc/nginx/conf.d/security*
rm -f /etc/nginx/conf.d/modsecurity*
rm -f /etc/nginx/conf.d/waf*
rm -f /etc/nginx/conf.d/az-*
rm -f /etc/nginx/conf.d/oryx-*
rm -f /etc/nginx/snippets/security*

# Write a clean nginx server config
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

# Write a clean main nginx config (no security includes)
cat > /etc/nginx/nginx.conf << 'NGINXMAIN'
user nginx;
worker_processes auto;
error_log /home/LogFiles/error.log warn;
pid /var/run/nginx.pid;
daemon off;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    access_log /home/LogFiles/access.log;
    sendfile on;
    tcp_nopush on;
    keepalive_timeout 65;
    gzip on;

    include /etc/nginx/sites-enabled/*;
}
NGINXMAIN

nginx -t 2>&1

exit 0
