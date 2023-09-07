# Mezzio Skeleton with Keycloak

This is a simple [Mezzio app](https://docs.mezzio.dev/mezzio/), based on the skeleton, to illustrate authenticating against a Keycloak server using OIDC and using `\Mezzio\Authentication` to take care of the persisted identity.

It's rudimentary at best, but was a useful exercise for me to make sense of the moving parts

## Pre-requisites

This demo works on the basis that you already have Traefik running in Docker and you are able to route traffic to specific hostnames.

[docker-compose.yml](docker-compose.yml), which you can edit, adds labels for the secured front-end _(Nginx -> php-fpm)_ and the Keycloak administration interface so that each of these respond to a configurable hostname.

Setting up Traefik is beyond the scope of this file, but generally, I set up Traefik in a local container bound to ports 80 and 443 on localhost and configure it to generate valid certs. I'll then set up `cloudflared` with a tunnel so that it becomes easy to route domain names directly to Traefik.

## Setup

First, copy the shipped [default.env](./default.env) file to `.env` in the root of the project and edit the environment variables to suit you.

- `MEZZIO_HOSTNAME` should be the FQDN of the public facing Mezzio website
- `KC_HOSTAME` should be the FQDN of the Keycloak server we'll be running
- `MK_REALM` and `MK_CLIENT_ID` is the realm and client id you'll setup in Keycloak
- `MK_CLIENT_SECRET` won't be known until Keycloak is ready to go, but you can make something up there until we have created it.

## Run

From the project root run:

```bash
docker compose up -d
```

## Configure Keycloak

You should then be able to log in to Keycloak at the hostname you configured. If you take a look at [docker-compose.yml](./docker-compose.yml), you'll see that the credentials for logging in to Keycloak are `admin` and `password`.

Once logged in to Keycloak, create a new realm with the name you entered in `MK_REALM`. We want to enable user registration so that we can set up an account like an end user would, so find 'Realm Settings', then the 'Login' tab and enable user registration.

Next, we need to add a new client, so navigate to "Clients" and add a new client. Use the value for "MK_CLIENT_ID" as the client id.

As you progress through the client setup process, you can leave most items as per defaults as long as you ensure that the "Standard Flow" is checked during capability config _(All other capabilities can be disabled)_, and "Client Authentication" is turned on. It's a good idea to restrict the valid redirect URIs where appropriate to the hostname of the web app, ie `MEZZIO_HOSTNAME` - use a wildcard here, for example, `https://example.com/*` if it suits you, or paste in the exact path.

Once you've finished creating the client, navigate to the "Credentials" tab for the client, copy 
the client secret and paste it into your `.env` file under `MK_CLIENT_SECRET` _(If you can't see the client secret or the 'Credentials' tab, make sure you've turned on 'Client Authentication' under the settings tab)_.

At this point, it's easiest to run `docker compose down && docker compose up -d` to apply the changes in env vars.

You should now be able to successfully register for an account and authenticate yourself via the Mezzio app and experiment with OTP setup, social identity providers and all that stuff.

It's worth mentioning that the [Keycloak provider used](https://github.com/stevenmaguire/oauth2-keycloak) does not support PKCE at the time of writing, however, PKCE _is_ supported upstream in [League OAuth Client](https://oauth2-client.thephpleague.com) and it is relatively trivial to create a custom provider to fill this missing feature. Keycloak itself will perform the exchange if requested, however if you enforce PKCE in Keycloak itself, this provider won't work OOTB.
