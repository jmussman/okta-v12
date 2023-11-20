![](../.common/joels-private-stock.png?raw=true)

# Okta V12 Server Endpoints

## Overview

Okta V12 may use a secure API tokens directly, or a client-id and secret to obtain an OAuth2 access token.
The API token or client-id and secret are managed and provide provided by the front-end application.
The API token or OAuth2 access token are used to connect to an Okta organization to check schema conformance.

API tokens and client credentials are not stored by this service, it is stateless.
They must be provided on each request.

The endpoints all follow the form: /verify/[schema-name]/[organization-url]/[credentials].
Credentials may be in the form of a single API token: /[apikey], or client credentials
to obtain an OAuth2 access token: /[client-id]/[client-secret].

## Example Endpoints

|Endpoint|Description|
|--------|-----------|
|/essentials|Checks the organization for conformance to the Okta Essentials Practical Lab.|

<hr>
Copyright © 2023 Joel Mussman. All rights reserved.