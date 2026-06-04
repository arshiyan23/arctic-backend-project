#!/bin/bash
CFG=/etc/nginx/sites-enabled/default

# Backup
cp $CFG /etc/nginx/sites-enabled/default.bak

# Change doc root to web/
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/web;|' $CFG
sed -i 's|/home/site/wwwroot/index |/home/site/wwwroot/web/index |' $CFG

# Copy nginx configs to web root for debugging
mkdir -p /home/site/wwwroot/web/nginx-debug
cp $CFG /home/site/wwwroot/web/nginx-debug/
find /etc/nginx -type f -name '*.conf' -exec cp {} /home/site/wwwroot/web/nginx-debug/ \;
find /etc/nginx -type f -name 'default' -exec cp {} /home/site/wwwroot/web/nginx-debug/ \;

exit 0
