<?php

// StepRunner
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// This parses and runs an individual step from the overall schema. The result is a JSON document in
// the form { "title": "$stepTitle", "result": true|false } where the $variables are placed by the value.
//
// A step has two parts: a title and an operation: "and", "or", "not", or "action". And, or, and not
// operations are a sequential array of actions, and act accordingly across that array. The "action"
// action is a set of properties defining the API query and cache operations to save results.
//

declare(strict_types=1);
namespace Src\Services;

use \InvalidArgumentException;
use \OutOfBoundsException;

use Src\Services\ActionRunner;

class StepRunner {

	private ActionRunner $actionRunner;

	public function __construct(ActionRunner $actionRunner) {

		$this->actionRunner = $actionRunner;
	}

	private function andOperator(array $actions): bool {

		// An "and" action is a sequential array of actions, they must all be true and processing stops on the
		// first false.

		if (array_keys($actions)[0] !== 0) {

			// This must be a sequential array, only a list of unlabled actions is allowed.

			throw new OutOfBoundsException();
		}

		$result = false;

		foreach ($actions as $action) {

			$result = $this->actionRunner->run($action);

			if (!$result) {

				break;
			}
		}

		return $result;
	}

	private function notOperator(array $actions): bool {

		// A "not" action is a sequential array of actions, they must all be false and processing stops on the
		// first true.

		if (array_keys($actions)[0] !== 0) {

			// This must be a sequential array, only a list of unlabled actions is allowed.

			throw new OutOfBoundsException();
		}

		$result = true;

		foreach ($actions as $action) {

			$result = !$this->actionRunner->run($action);

			if (!$result) {

				break;
			}
		}

		return $result;
	}

	private function orOperator(array $actions): bool {

		// An "or" action is a sequential array of actions, any one may be true and processing stops on the
		// first true.

		if (array_keys($actions)[0] !== 0) {

			// This must be a sequential array, only a list of unlabled actions is allowed.

			throw new OutOfBoundsException();
		}

		$result = false;

		foreach ($actions as $action) {

			$result = $this->actionRunner->run($action);

			if ($result) {

				break;
			}
		}

		return $result;
	}

	public function run(array $components) {

		// All of the actions in a step must evalulate to true for the step to be successful. There
		// may be multiple "and", "or", or "not" actions but they must evalulate appropriately.
		// There is a "title" for the step.

		if (!array_key_exists('title', $components) || !is_string($components['title'])) {

			throw new InvalidArgumentException();
		}

		$result = [ 'title' => $components['title'],
					'result' => null ];

		// There can be only one title and one action, but we use a loop because it's
		// the easiest way to get the key and the value and decide how to handle it.
		// Note that only if it is the title do skip over it; otherwise we execute
		// the single action or throw an exception for something unrecognized.

		foreach ($components as $key => $value) {

			switch ($key) {

				case 'title':
					break;

				case 'and':
					$result['result'] = $this->andOperator($value);
					break;
		
				case 'not':
					$result['result'] = $this->notOperator($value);
					break;
			
				case 'or':
					$result['result'] = $this->orOperator($value);
					break;
		
				case 'action':
					$result['result'] = $this->actionRunner->run($value);
					break;
		
				default:
					throw new InvalidArgumentException();
					break;
				}

			if ($result['result'] != NULL) {

				break;
			}
		}

		return $result;
	}
}