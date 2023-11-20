<?php

// PartRunnerTest
// Copyriht © 2023 Joel A Mussman. All rights reserved.
//

declare(strict_types=1);
namespace Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use \Exception;
use \OutOfBoundsException;
use \TypeError;

use Src\Services\PartRunner;
use Src\Services\StepRunner;

final class PartRunnerTest extends TestCase {

	private array $part;
	private PartRunner $partRunner;
	private StepRunner $stepRunnerMock;

	#[Before]
	protected function setup(): void {

		$this->stepRunnerMock = $this->createMock(StepRunner::class);

		$this->part = [ 'title' => 'Part 1', 'steps' => [
			[ 'isEvent' => [ 'filter' => 'eventType eq group.lifecycle.create' ]],
			[ 'isEvent' => [ 'filter' => 'eventType eq group.user_membership.add' ]]
		]];

		$this->partRunner = new PartRunner($this->stepRunnerMock);
	}

	#[Test]
	public function runEachStep(): void {

		$this->stepRunnerMock->method('run')->willReturnCallback(fn($step) => [ 'title' => 'Step X', 'result' => true ]);

		$result = $this->partRunner->run($this->part);

		$this->assertEquals(2, count($result));
	}

	#[Test]
	public function rejectNullPart(): void {

		$this->expectException(TypeError::class);

		$this->partRunner->run(NULL);
	}

	#[Test]
	public function rejectPartNotArray(): void {

		$this->expectException(TypeError::class);

		$this->partRunner->run(0);
	}

	#[Test]
	public function rejectPartWithError(): void {

		$this->stepRunnerMock->method('run')->willThrowException(new OutOfBoundsException());

		$this->expectException(Exception::class);

		$this->partRunner->run($this->part);
	}
}