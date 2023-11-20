<?php

// VerifierServiceTest
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
use Src\Services\VerifierService;

final class VerifierServiceTest extends TestCase {

	private array $schema;
	private SchemaRunner $schemaRunnerMock;
	private VerifierService $verifierService;

	#[Before]
	protected function setup(): void {

		$this->schemaRunnerMock = $this->createMock(SchemaRunner::class);

		$this->schema = [
			[ 'title' => 'Part 1', 'steps' => [] ],
			[ 'title' => 'Part 2', 'steps' => [] ],
		];

		$this->verifierService = new VerifierService($this->schemaRunnerMock);
	}

	#[Test]
	public function runEachPart(): void {

		$this->schemaRunnerMock->method('run')->willReturnCallback(fn($part) => [ 'title' => 'Part X', 'result' => true ]);

		$result = $this->verifierService->run($this->schema);

		$this->assertEquals(2, count($result));
	}

	#[Test]
	public function rejectNullSchema(): void {

		$this->expectException(TypeError::class);

		$this->verifierService->run(NULL);
	}

	#[Test]
	public function rejectPartWithError(): void {

		$this->schemaRunnerMock->method('run')->willThrowException(new OutOfBoundsException());

		$this->expectException(Exception::class);

		$this->verifierService->run($this->schema);
	}

	#[Test]
	public function rejectSchemaNotArray(): void {

		$this->expectException(TypeError::class);

		$this->verifierService->run(0);
	}
}