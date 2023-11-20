![](.common/joels-private-stock.png?raw=true)

# Okta V12 Organization Verification Application

## Overview

The V12 project structure is a front-end application and a resource server which communicates with the Okta Management API.

The resource server has the ability to use either an API token or an OAuth2 Access token to query the API.
An API token should have Read-Only Administrator privileges.
To provide an access token the server needs a client id and secret bound to an application integration.
The integration needs to grant privileges for all of the API areas the schema touches, primarly logs and groups fo
most scenarios.

The API tokens or client credentials are managed in the front-end by the user, the *reviwer*,
and added hrough the UI application along with the organization URL.
The API token or client credentials, along with the organization URL and the schema to check,
are passed to the server on each request for an update, the server is
not stateful and does not record them.

The report from the server consists of true or false results for each step of the verification,
which may be translated by the UI into a visual representation of conformance to the requirements.

The server has no dependency on the front-end.
Multiple front-ends may be created, including automated services that verify the conformance of an organization.
The server is designed to support an Oauth2 access token for access, which reduces inappropriate usage.

## Conformance Schemas

The JSON schemas are stored and utilized on the server side.
The structure of the schema file is described in the [schema.md](./Resources/schema.md) file.
The schema required by the client for verification is requested by the client along with the organization URL and the API token
or client credentials.

## Server Endpoints

The server has a unique endpoint for each schema.
The format to call the endpoints is defined in the [endpoints document](./Resources./endpoints.md).

<hr>
Copyright © 2023 Joel Mussman. All rights reserved.