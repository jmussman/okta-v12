<?php

// SchemaRunnerTest
// Copyriht © 2023 Joel A Mussman. All rights reserved.
//

declare(strict_types=1);
namespace Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use \Exception;
use \OutOfBoundsException;
use \TypeError;

use Src\Services\SchemaRunner;
use Src\Services\PartRunner;

final class SchemaRunnerTest extends TestCase {

	private PartRunner $partRunnerMock;
	private array $schema;
	private SchemaRunner $schemaRunner;

	#[Before]
	protected function setup(): void {

		$this->partRunnerMock = $this->createMock(PartRunner::class);

		$this->schema = [
			[ 'title' => 'Part 1', 'steps' => [] ],
			[ 'title' => 'Part 2', 'steps' => [] ],
		];

		$this->schemaRunner = new SchemaRunner($this->partRunnerMock);
	}

	#[Test]
	public function runEachPart(): void {

		$this->partRunnerMock->method('run')->willReturnCallback(fn($part) => [ 'title' => 'Part X', 'result' => true ]);

		$result = $this->schemaRunner->run($this->schema);

		$this->assertEquals(2, count($result));
	}

	#[Test]
	public function rejectNullSchema(): void {

		$this->expectException(TypeError::class);

		$this->schemaRunner->run(NULL);
	}

	#[Test]
	public function rejectPartWithError(): void {

		$this->partRunnerMock->method('run')->willThrowException(new OutOfBoundsException());

		$this->expectException(Exception::class);

		$this->schemaRunner->run($this->schema);
	}

	#[Test]
	public function rejectSchemaNotArray(): void {

		$this->expectException(TypeError::class);

		$this->schemaRunner->run(0);
	}
}