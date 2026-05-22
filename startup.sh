#!/bin/bash
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/web;|' /etc/nginx/sites-enabled/default
