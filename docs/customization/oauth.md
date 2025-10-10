## Authelia

To enable Authelia OAuth, add:

```yaml
environment:
  - AUTHELIA_CLIENT_ID=CLIENT_ID # change id
  - AUTHELIA_CLIENT_SECRET=CLIENT_SECRET # change secret
  - AUTHELIA_REDIRECT_URI=http://localhost/oauth/authelia/callback # change domain
  - AUTHELIA_BASE_URL=http://your-authelia-url # change url
```

## Authentik

To enable Authentik OAuth, add:

```yaml
environment:
  - AUTHENTIK_CLIENT_ID=CLIENT_ID # change id
  - AUTHENTIK_CLIENT_SECRET=CLIENT_SECRET # change secret
  - AUTHENTIK_REDIRECT_URI=http://localhost/oauth/authentik/callback # change domain
  - AUTHENTIK_BASE_URL=http://your-authentik-url # change url
```

## Azure

To enable Azure OAuth, add:

```yaml
environment:
  - AZURE_CLIENT_ID=CLIENT_ID # change id
  - AZURE_CLIENT_SECRET=CLIENT_SECRET # change secret
  - AZURE_TENANT_ID=TENANT_ID # change id
  - AZURE_PROXY=http://your-proxy-url # change url (optional configuration)
```

## Keycloak

To enable Keycloak OAuth, add:

```yaml
environment:
  - KEYCLOAK_CLIENT_ID=CLIENT_ID # change id
  - KEYCLOAK_CLIENT_SECRET=CLIENT_SECRET # change secret
  - KEYCLOAK_REDIRECT_URI=http://localhost/oauth/keycloak/callback # change domain
  - KEYCLOAK_BASE_URL=http://your-keycloak-url # change url
  - KEYCLOAK_REALM=YOUR_REALM # change realm
```

## Pocket ID

To enable Pocket ID OAuth, add:

```yaml
environment:
  - POCKETID_CLIENT_ID=CLIENT_ID # change id
  - POCKETID_CLIENT_SECRET=CLIENT_SECRET # change secret
  - POCKETID_REDIRECT_URI=http://localhost/oauth/pocketid/callback # change domain
  - POCKETID_BASE_URL=http://your-pocketid-url # change url
```

## Zitadel

To enable Zitadel OAuth, add:

```yaml
environment:
  - ZITADEL_CLIENT_ID=CLIENT_ID # change id
  - ZITADEL_CLIENT_SECRET=CLIENT_SECRET # change secret
  - ZITADEL_REDIRECT_URI=http://localhost/oauth/zitadel/callback # change domain
  - ZITADEL_BASE_URL=http://your-zitadel-url # change url
  - ZITADEL_ORGANIZATION_ID=ORGANIZATION_ID # change id (optional configuration)
  - ZITADEL_PROJECT_ID=PROJECT_ID # change id (optional configuration)
```
