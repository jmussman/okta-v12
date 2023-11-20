<?php

// QueryBuilder
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// Build the headers and full URI path for the API call.
//

declare(strict_types=1);
namespace Src\Services;

use \InvalidArgumentException;

class QueryBuilder {

	const HEADERSKEY = 'headers';
	const PATHKEY = 'path';

	public function build(string $url, string $api, string $queryString, string $apikey) : array {

		if (!$url || !$api || !$apikey) {

			// This catches any empty strings, but the method still just uses whatever is sent if everything has a value.

			throw new InvalidArgumentException();
		}

		$headers = [];

		$headers[] = 'Content-Length: 0';
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: application/json';
		$headers[] = 'Authorization: SSWS ' . $apikey;

		$path = $url . '/oauth2/v1' . $api;
		
		if ($queryString) {
			
			$path .= '?' . $queryString;
		}

		$result = [];

		$result['headers'] = $headers;
		$result['path'] = $path;
		
		return $result;
	}
}