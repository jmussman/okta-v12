![](../.common/joels-private-stock.png?raw=true)

# Okta V12 Conformance Schemas

The Okta V12 VerifyService allows the Okta V12 engine to verify a JSON conformance schema with configuration characteristics
against an Okta organization.
The service provides the engine used by the controllers to perform the criteria check against an organization.
In the footsteps of many NoSQL databases, it is and engine that reads a language constructed from JSON,
where operations are encoded as properties of objects.
Of course, most of the properties are merely data.
This document explains the schema to build a verification criteria.
More details about the service can be found in the [README file](../README.md) and with the [server architecture file](./server.md)

## Schema Construction
### Parts and Steps

The whole schema has "parts", each part has a title.
A step has a "title".
Steps have a single action, or a single array of actions labled with "and", "or", or "not".
Some of these properties are examples, properties and values flagged with $ are described later in the schema.
For example, $action in the following example represents a complete action:

```
{
	"parts": [
		{
			"title": "Part 1"
			"steps": [
				{
					"title": "John Smith",
					"action": {
						"api": "/logs",
						"filter": "eventType eq \"user.lifecycle.create\" and target.alternateId sw \"john.smith@oktaice\"",
						"cache": "target.id"
					},
					"and": [ $action, $action... ],
					"or": [ $action, $action... ],
					"not": [ $action, $action... ]
				}
```

Only one property of "action", "and", "or", or "not" may be in a step.

### Characteristics in an Action

Actions have three types of characteristic properties.
"api" is used to specify which API the query is to be directed at.
The default api is /logs if nothing is stated.

The second fixed characteristic is the "cache" operator.
This is used to cache a specific target value from the query results after it is executed.
Cached values may be referenced using the same cache path in subsequent actions, and will be expanded into the action before
the query is executed.
Cache values cross steps and parts.
Cache operations are not required, and not used unless there is data to capture.

Every othe property is a query string argument that matches API request as documented at Okta.
For example, a filter is a common property for logs and other groups of objects: users, groups, etc.
The search property is a common argument when querying for rules, etc.

```
"and": [
	{
		"api": "/logs",
		"filter": "eventType eq \"user.lifecycle.create\" and target.alternateId sw \"john.smith@oktaice\"",
		"cache.johnSmith.id": "target.id"
	},
	{
		"filter": "eventType eq \"group.lifecycle.create\" and target.displayName eq \"Marketing\"",
		"cache.marketingGroup.id": "target.id"
	},
	{
		"api": "/groups",
		"filter": "eventType eq \"group.user_membership.add\" and target.id eq \"cache.marketingGroup.id\" and target.id eq \"cache.johnSmith.id\""
	}
]
```

In this example the creation of the user John Smith was verified.
Because the username domain changes, "sw" was used instead of "eq" for matching the correct user.
John's unique user id is cached.

The second action verifies the creation of the Marketing group.
The unique group id is cached.

The third action verifies that John was added to the Marketing group, note the expansion of the two cached values in the filter to
identify the specific group and user.

While these three actions were put together under an "and", in reality the best practice is to
split each into a separate step.
With separate steps the user can see that each was completed, rather than one status for them lumped together.
The cache values will cross the step boundary.

## Verification Results

Results are returned as a JSON document.
The document is an array of parts, divided into steps.
Each part and step retains its title.
Each step has a result of *true* or *false*.

```
{
	"parts": [
		{
			"title": "Part 1",
			"steps": [
				{
					"title": "John Smith",
					"result": true
				}
			]
```

This allows a UI to build a list of the steps, perhaps a veritical list or a row of small colored blocks to hover over, and
show what passed and what faield.

<hr>
Copyright © 2023 Joel Mussman. All rights reserved.