<?php

// PartRunner
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// This parses and runs an individual part from the overall schema. The result is a JSON document in
// the form { "title": "$partTitle", "result": true|false } where the $variables are placed by the value.
//
// A part has two properties: title and steps, and sequential array of steps.
//

declare(strict_types=1);
namespace Src\Services;

use Src\Services\StepRunner;

class PartRunner {

	private StepRunner $stepRunner;

	public function __construct(StepRunner $stepRunner) {

		$this->stepRunner = $stepRunner;
	}

	public function run(array $part): array {

		// A schema is made up of an array of parts; each mart has a title and steps. The result of running the steps
		// is placed in the "steps" property of the array.

		if (!array_key_exists('title', $part) || !is_string($part['title']) || !array_key_exists('steps', $part) || !is_array($part['steps'])) {

			throw new InvalidArgumentException();
		}

		$result = [ 'title' => $part['title'],
					'steps' => [] ];

		foreach ($part["steps"] as $step) {

			$result["steps"][] = $this->stepRunner->run($step);
		}

		return $result;
	}
}