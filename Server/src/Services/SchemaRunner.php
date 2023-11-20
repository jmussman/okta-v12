<?php

// SchemaRunner
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// This parses and runs an individual part from the overall schema. The result is a JSON document containing
// an array of the part results. See the PartRunner and StepRunner for details on what they return.
//

namespace Src\Services;

use Src\Services\PartRunner;

class SchemaRunner {

	private PartRunner $partRunner;

	public function __construct(PartRunner $partRunner) {

		$this->partRunner = $partRunner;
	}

	public function run(array $schema): array {

		$result = [];

		foreach ($schema as $part) {

			$result[] = $this->partRunner->run($part);
		}

		return $result;
	}
}