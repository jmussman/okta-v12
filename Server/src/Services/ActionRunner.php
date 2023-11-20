<?php

// ActionRunner
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// This identifies and parses an action. Actions have the structure:
//
// 	{
//		"api": "$value",			// Possible API path, defaults to "/logs".
//		"operation": "$value"		// Operations depend on the API: filter, search, etc. Multiple operations allowed.
//		"cache.?": "$target"		// Cache values from a successful query, which may be used in later queries. Multiples allowed.
//	}
//
// After the operations list is built, the url, api, and operations are built into a query with the credentials
// and executed agains the API.
//
// On a successful query, any cache operations are exectued to build the cache out from that query result.
//

declare(strict_types=1);
namespace Src\Services;

use \InvalidArgumentException;
use \OutOfBoundsException;

use Src\Services\Cache;
use Src\Services\Runner;
use Src\Services\QueryBuilder;
use Src\Services\QueryRunner;

class ActionRunner {

	const CACHEACTION = 'cache.';
	const LOGSAPI = '/logs';

	private string $apikey;
	private Cache $cache;
	private QueryBuilder $queryBuilder;
	private QueryRunner $queryRunner;
	private string $url;

	public function __construct(string $url, string $apikey, Cache $cache, QueryBuilder $queryBuilder, QueryRunner $queryRunner) {

		if (!$url || !$apikey) {

			throw new InvalidArgumentException();
		}

		$this->apikey = $apikey;
		$this->cache = $cache;
		$this->queryBuilder = $queryBuilder;
		$this->queryRunner = $queryRunner;
		$this->url = $url;
	}

	private function buildCache($api, $queryResult, $characteristics): void {

		// Are we caching values from the target array (log entry) or the whole result (object query)?
		
		$targets = ($api == '/logs') ? $queryResult[0]['target'] : $queryResult;

		foreach ($characteristics as $key => $target) {

			if (strpos($key, self::CACHEACTION, 0) === 0) {

				// This is a cache command.

				if ($api == '/logs') {

					if (!str_starts_with($target, 'target.')) {

						throw new InvalidArgumentException();
					}

					$target = substr($target, strlen('target.'));
				}

				if (!$this->cache->add($key, $target, $queryResult)) {

					throw new OutOfBoundsException();
				};
			}
		}
	}

	private function buildQueryString(array $characteristics) : string {

		$queryString = '';

		foreach ($characteristics as $characteristic => $value) {

			if ($characteristic == 'api' || strpos($characteristic, self::CACHEACTION, 0) === 0) {

				// Ignore, api and cache actions are handled above this method.

				continue;
			}

			if (strlen($queryString)) {

				$queryString .= '&';
			}

			$queryString .= $characteristic . '=';
			$queryString .= $this->resolve($value);	// Resolve any cache references to their actual value.
		}

		return $queryString;
	}

	private function resolve(string $value): string {

		// Replace any cache references in the value with the corresponding value from the cache.
		// First find all the cache targets in the value.

		$matches = [];
		$matchCount = preg_match_all('/(cache\..*?)(?=")/', $value, $matches);

		if ($matchCount) {
				
			$matches = $matches[0];
			$matches = array_unique($matches);

			// Build a list of targets and cache values.

			$replacement = [];

			foreach ($matches as $match) {

				$value = str_replace($match, $this->cache->resolve($match), $value);
			}
		}

		return $value;
	}

	public function run(array $component): bool {

		$result = false;

		// Build the API URL with the query string.

		$queryString = $this->buildQueryString($component);

		$api = self::LOGSAPI;

		if (array_key_exists('api', $component)) {

			$api = $component['api'];

		}

		// Build and execute the query.

		$query = $this->queryBuilder->build($this->url, $api, $queryString, $this->apikey);
		$jsonResult = $this->queryRunner->run($query[QueryBuilder::HEADERSKEY], $query[QueryBuilder::PATHKEY]);

		// Any single object is an error from the API.

		if (!(strpos($jsonResult, '{', 0) === 0)) {

			// Decode the JSON into an associative array.

			$queryResult = json_decode($jsonResult, true);

			// An empty array is a failure (could be success if we are looking for absence, but that is handled above this class with "not").

			if (count(array_keys($queryResult))) {		

				$result = true;

				// Handle any cache operations with the result.

				$this->buildCache($api, $queryResult, $component);
			}
		}

		return $result;
	}
}