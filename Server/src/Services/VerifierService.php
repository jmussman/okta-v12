<?php
// VerifierService
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// The controller which uses this service will instantiate a new one, and the controller runs in a
// separate PHP instance. PHP does not support singletons in this scenario, so there is no conflict
// with multithreading and instance variables all the way down this chain.
//
// OKta System Log vs. API queries idiosyncracies of note:
//
//		1. In the System Log everything is capitalized; in the filter and the JSON result
//			the properties are camel-case.
//
//		2. In the System Log the Event is a hierarchy behind Actor & Client; in the JSON result
//			There is not Event property, its properties appear at the same level as Actor,
//			Client, Request, and Target.
//
//		3. In the System Log the multiple target emtroes appear at the same level; in the JSON
//			result the target property is an array of targets.
//

namespace Src\Services;

use Src\Services\SchemaRunner;

class VerifierService
{

	private $schemaRunner;

	public function __construct(SchemaRunner $schemaRunner) {

		$this->schemaRunner = $schemaRunner;
	}

	public function run(array $schema): array {

		return $this->schemaRunner->run($schema);
	}
}