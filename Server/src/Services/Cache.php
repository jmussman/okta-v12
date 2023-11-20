<?php

// Cache
// Copyright © Joel A Mussman. All rights reserved.
//
// The cache is shared across built in the action runner, resolved in the characteristic filters, and shared
// across all parts and steps. The source is the array of targets from the query results. The word 'target'
// in the path means the first target with a property that matches.
//

declare(strict_types=1);
namespace Src\Services;

use \InvalidArgumentException;
use \UnexpectedValueException;

class Cache {

	private $cache = [];

	public function add(string $key, string $path, array $source): bool {

		if (!$key || !$path || !$source) {

			throw new InvalidArgumentException();
		}

		$this->cache[$key] = $this->resolveArrayPath($path, $source);

		return $this->cache[$key] ? true : false;
	}

	private function resolveArrayPath(string $path, array $source): ?string {
		
		$result = null;

		// This resolves a target path from a nested associative array, the query result.

		$components = explode(".", $path);

		foreach ($source as $target) {

			// There may be multiple target sources and we have to check each one until we find a match. If the
			// source is not an array, that doesn't make any sense (corrupt data) but we ignore it.

			$drillTarget = $target;

			if (is_array($drillTarget)) {

				foreach ($components as $component) {

					$drillTarget = array_key_exists($component, $drillTarget) ? $drillTarget[$component] : NULL;

					if (!$drillTarget) {

						break;
					}
				}

				if ($drillTarget) {

					// Found the target, so stop checking additional sources.

					$result = $drillTarget;
					break;
				}
			}
		}

		return $result;
	}

	public function resolve(string $path): string {

		if (!$path) {

			throw new InvalidArgumentException();
		}

		if (!array_key_exists($path, $this->cache)) {

			throw new UnexpectedValueException();
		}

		return $this->cache[$path];
	}
}