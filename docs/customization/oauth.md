# OAuth

OpenID Connect works with most providers and is the best place to start. Other providers are listed at the end.

If you have trouble setting up a provider, see [how to debug the application](../support/faqs.md#how-to-debug-the-application) to find the error.

## OpenID Connect

Works with any login provider that supports OpenID Connect discovery. Endpoints and signing keys are read from `OIDC_BASE_URL/.well-known/openid-configuration`, so there are no other URLs to configure.

To enable OpenID Connect, add:

```yaml
environment:
  - OIDC_NAME=Your provider # name shown on the login button
  - OIDC_CLIENT_ID=CLIENT_ID # change id
  - OIDC_CLIENT_SECRET=CLIENT_SECRET # change secret
  - OIDC_REDIRECT_URI=http://localhost/oauth/oidc/callback # change domain
  - OIDC_BASE_URL=https://your-provider-url # change url
```

The provider URL must use `https`, unless it points to `localhost`.

### Optional settings

```yaml
environment:
  - OIDC_SCOPES=openid profile email groups # change scopes if your provider needs others
  - OIDC_EMAIL_CLAIMS=email # change claim if your provider returns the email elsewhere
  - OIDC_CLOCK_SKEW=0 # change tolerance in seconds if your server clock drifts
  - OIDC_PROXY=http://your-proxy-url # change url
  - OIDC_VERIFY_JWT=true # set to false only if your provider cannot serve a JWKS
  - OIDC_TOKEN_AUTH_METHOD=client_secret_post # or client_secret_basic
```

`OIDC_TOKEN_AUTH_METHOD` is detected from the provider, so set it only if authentication fails because your provider advertises a method it does not accept. The only valid values are `client_secret_basic` and `client_secret_post`, written exactly like that. Any other value, including `basic` or `post`, is treated as `client_secret_post` without warning.

### Multiple providers

To use more than one OpenID Connect provider at the same time, list a name for each one in `OIDC_PROVIDERS` and configure it with `OIDC_{NAME}_` variables. Every name becomes its own login button and its own callback URL, `/oauth/oidc_{name}/callback`.

Names may only contain letters, digits, and underscores, and are lowercased to build the callback URL. Any other name is ignored.

For example, to enable both Pocket ID and a company provider, add:

```yaml
environment:
  - OIDC_PROVIDERS=pocketid,corp # change names, separated by commas
  # first provider:
  - OIDC_POCKETID_NAME=Pocket ID # name shown on the login button
  - OIDC_POCKETID_CLIENT_ID=CLIENT_ID # change id
  - OIDC_POCKETID_CLIENT_SECRET=CLIENT_SECRET # change secret
  - OIDC_POCKETID_REDIRECT_URI=http://localhost/oauth/oidc_pocketid/callback # change domain
  - OIDC_POCKETID_BASE_URL=https://your-pocketid-url # change url
  # second provider:
  - OIDC_CORP_NAME=Corp SSO # name shown on the login button
  - OIDC_CORP_CLIENT_ID=CLIENT_ID # change id
  - OIDC_CORP_CLIENT_SECRET=CLIENT_SECRET # change secret
  - OIDC_CORP_REDIRECT_URI=http://localhost/oauth/oidc_corp/callback # change domain
  - OIDC_CORP_BASE_URL=https://your-corp-url # change url
```

If `OIDC_{NAME}_NAME` is omitted, the name given in `OIDC_PROVIDERS` is used on the login button. The optional settings above are available per provider too, such as `OIDC_CORP_SCOPES`.

Each provider is independent, with its own credentials and its own discovery document. The plain `OIDC_` variables still work and can be combined with named providers.

Keep the names stable. They are part of how accounts are linked, so renaming a provider unlinks the accounts created with it.

## Other providers

Use these only if OpenID Connect does not work for your service, or if you need a setting it does not offer.

Bitbucket, Facebook, GitHub, GitLab, Google, LinkedIn, Slack, and Twitter need only the three standard variables. For example, to enable GitHub, add:

```yaml
environment:
  - GITHUB_CLIENT_ID=CLIENT_ID # change id
  - GITHUB_CLIENT_SECRET=CLIENT_SECRET # change secret
  - GITHUB_REDIRECT_URI=http://localhost/oauth/github/callback # change domain
```

The providers below need extra variables.

### Auth0

```yaml
environment:
  - AUTH0_CLIENT_ID=CLIENT_ID # change id
  - AUTH0_CLIENT_SECRET=CLIENT_SECRET # change secret
  - AUTH0_REDIRECT_URI=http://localhost/oauth/auth0/callback # change domain
  - AUTH0_BASE_URL=https://your-auth0-url # change url
```

### Authelia

```yaml
environment:
  - AUTHELIA_CLIENT_ID=CLIENT_ID # change id
  - AUTHELIA_CLIENT_SECRET=CLIENT_SECRET # change secret
  - AUTHELIA_REDIRECT_URI=http://localhost/oauth/authelia/callback # change domain
  - AUTHELIA_BASE_URL=https://your-authelia-url # change url
```

### Authentik

```yaml
environment:
  - AUTHENTIK_CLIENT_ID=CLIENT_ID # change id
  - AUTHENTIK_CLIENT_SECRET=CLIENT_SECRET # change secret
  - AUTHENTIK_REDIRECT_URI=http://localhost/oauth/authentik/callback # change domain
  - AUTHENTIK_BASE_URL=https://your-authentik-url # change url
```

### Azure

```yaml
environment:
  - AZURE_CLIENT_ID=CLIENT_ID # change id
  - AZURE_CLIENT_SECRET=CLIENT_SECRET # change secret
  - AZURE_REDIRECT_URI=http://localhost/oauth/azure/callback # change domain
  - AZURE_TENANT_ID=TENANT_ID # change id
  - AZURE_PROXY=http://your-proxy-url # change url (optional configuration)
```

### Keycloak

```yaml
environment:
  - KEYCLOAK_CLIENT_ID=CLIENT_ID # change id
  - KEYCLOAK_CLIENT_SECRET=CLIENT_SECRET # change secret
  - KEYCLOAK_REDIRECT_URI=http://localhost/oauth/keycloak/callback # change domain
  - KEYCLOAK_BASE_URL=https://your-keycloak-url # change url
  - KEYCLOAK_REALM=YOUR_REALM # change realm
```

### Pocket ID

```yaml
environment:
  - POCKETID_CLIENT_ID=CLIENT_ID # change id
  - POCKETID_CLIENT_SECRET=CLIENT_SECRET # change secret
  - POCKETID_REDIRECT_URI=http://localhost/oauth/pocketid/callback # change domain
  - POCKETID_BASE_URL=https://your-pocketid-url # change url
  - POCKETID_USE_PKCE=false # set to true if you want to use PKCE (optional configuration)
```

### Zitadel

```yaml
environment:
  - ZITADEL_CLIENT_ID=CLIENT_ID # change id
  - ZITADEL_CLIENT_SECRET=CLIENT_SECRET # change secret
  - ZITADEL_REDIRECT_URI=http://localhost/oauth/zitadel/callback # change domain
  - ZITADEL_BASE_URL=https://your-zitadel-url # change url
  - ZITADEL_ORGANIZATION_ID=ORGANIZATION_ID # change id (optional configuration)
  - ZITADEL_PROJECT_ID=PROJECT_ID # change id (optional configuration)
```
