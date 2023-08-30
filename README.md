# Mezzio Skeleton with Keycloak

## Pre-requisites

This demo works on the basis that you already have Traefik running in Docker and you are able to route traffic to specific hostnames.

[docker-compose.yml](docker-compose.yml), which you can edit, adds labels for the secured front-end _(Nginx -> php-fpm)_ and the Keycloak administration interface so that each of these respond to a configurable hostname.

Setting up Traefik is beyond the scope of this file, but generally, I set up Traefik in a local container bound to ports 80 and 443 on localhost and configure it to generate valid certs. I'll then set up `cloudflared` with a tunnel so that it becomes easy to route domain names directly to Traefik.

## Setup
