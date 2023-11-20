<?php

// StepRunnerTest
// Copyriht © 2023 Joel A Mussman. All rights reserved.
//

declare(strict_types=1);
namespace Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use \Exception;
use \InvalidArgumentException;
use \OutOfBoundsException;
use \TypeError;

use Src\Services\ActionRunner;
use Src\Services\StepRunner;

final class StepRunnerTest extends TestCase {

	private ActionRunner $actionRunnerMock;
	private array $andTwoStep;
	private int $callCounter;
	private array $notTwoStep;
	private array $orTwoStep;
	private array $step;
	private StepRunner $stepRunner;

	#[Before]
	protected function setup(): void {

		$this->actionRunnerMock = $this->createMock(ActionRunner::class);

		$this->step = [ 'title' => 'John Smith', 'action' => [ 'filter' => 'eventType eq user.lifecycle.create' ]];

		$this->andTwoStep = [ 'title' => 'John Smith', 'and' => [
								[ 'filter' => 'eventType eq group.user_membership.add' ],
								[ 'filter' => 'target.id eq cache.johnSmith.id' ]]];

		$this->notTwoStep = [ 'title' => 'John Smith', 'not' => [
								[ 'filter' => 'eventType eq group.user_membership.add' ],
								[ 'filter' => 'target.id eq cache.johnSmith.id' ]]];
								
		$this->orTwoStep = [ 'title' => 'John Smith', 'or' => [
								[ 'filter' => 'eventType eq group.user_membership.add' ],
								[ 'filter' => 'target.id eq cache.johnSmith.id' ]]];
	
		$this->stepRunner = new StepRunner($this->actionRunnerMock);
	}

	#[Test]
	public function falseAndFirstActionFalse(): void {

		$this->actionRunnerMock->expects($this->once())->method('run')->willReturn(false);

		$this->stepRunner->run($this->andTwoStep);
	}

	#[Test]
	public function falseAndSecondActionFalse(): void {

		$this->callCounter = 0;

		$this->actionRunnerMock->expects($this->exactly(2))->method('run')->willReturnCallback(fn() => ++$this->callCounter === 1);

		$this->stepRunner->run($this->andTwoStep);
	}

	#[Test]
	public function falseNotFirstActionTrue(): void {

		$this->actionRunnerMock->expects($this->once())->method('run')->willReturn(true);

		$this->stepRunner->run($this->notTwoStep);
	}

	#[Test]
	public function falseNotSecondActionTrue(): void {

		$this->callCounter = 0;

		$this->actionRunnerMock->expects($this->exactly(2))->method('run')->willReturnCallback(fn() => ++$this->callCounter === 2);

		$this->stepRunner->run($this->notTwoStep);

	}

	#[Test]
	public function falseNot(): void {

		$this->actionRunnerMock->method('run')->willReturn(true);

		$result = $this->stepRunner->run($this->notTwoStep);

		$this->assertFalse($result['result']);
	}

	#[Test]
	public function falseOr(): void {
	
		$this->actionRunnerMock->method('run')->willReturn(false);

		$result = $this->stepRunner->run($this->orTwoStep);

		$this->assertFalse($result['result']);
	}
	
	#[Test]
	public function rejectNestedOperation(): void {

		$step = [ 'title' => 'John Smith',
					'and' => [
						'or' => [
							[ 'filter' => 'eventType eq group.user_membership.add' ],
							[ 'filter' => 'target.id eq cache.johnSmith.id' ]]]];

		$this->expectException(OutOfBoundsException::class);

		$this->stepRunner->run($step);
	}

	#[Test]
	public function rejectNullStep(): void {

		$this->expectException(TypeError::class);

		$this->stepRunner->run(NULL);
	}

	#[Test]
	public function rejectStepNotArray(): void {

		$this->expectException(TypeError::class);

		$this->stepRunner->run(0);
	}

	#[Test]
	public function rejectStepWithError(): void {

		$this->actionRunnerMock->method('run')->willThrowException(new OutOfBoundsException());

		$this->expectException(Exception::class);

		$this->stepRunner->run($this->step);
	}

	#[Test]
	public function rejectUnrecognizedOperationLabel(): void {

		$step = [ 'title' => 'John Smith',
					'join' => [
						[ 'filter' => 'eventType eq group.user_membership.add' ],
						[ 'filter' => 'target.id eq cache.johnSmith.id' ]]];

		$this->expectException(InvalidArgumentException::class);

		$this->stepRunner->run($step);
	}

	#[Test]
	public function runStepFalse(): void {

		$this->actionRunnerMock->method('run')->willReturnCallback(fn($action) => false);

		$result = $this->stepRunner->run($this->step);

		$this->assertFalse($result['result']);
	}

	#[Test]
	public function runStepTitleMatch(): void {

		$this->actionRunnerMock->method('run')->willReturnCallback(fn($action) => true);

		$result = $this->stepRunner->run($this->step);

		$this->assertEquals($this->step['title'], $result['title']);
	}

	#[Test]
	public function runStepTrue(): void {

		$this->actionRunnerMock->method('run')->willReturnCallback(fn($action) => true);

		$result = $this->stepRunner->run($this->step);

		$this->assertTrue($result['result']);
	}

	#[Test]
	public function runActionReturnsTitle(): void {

		$this->actionRunnerMock->method('run')->willReturnCallback(fn($action) => true);

		$result = $this->stepRunner->run($this->step);

		$this->assertTrue(array_key_exists('title', $result));
	}

	#[Test]
	public function trueAnd(): void {

		$this->actionRunnerMock->method('run')->willReturn(true);

		$result = $this->stepRunner->run($this->andTwoStep);

		$this->assertTrue($result['result']);
	}

	#[Test]
	public function trueAndAllActions(): void {

		$this->actionRunnerMock->expects($this->exactly(2))->method('run')->willReturn(true);

		$this->stepRunner->run($this->andTwoStep);
	}

	#[Test]
	public function trueNot(): void {

		$this->actionRunnerMock->method('run')->willReturn(false);

		$result = $this->stepRunner->run($this->notTwoStep);

		$this->assertTrue($result['result']);
	}

	#[Test]
	public function trueNotAllActions(): void {

		$this->actionRunnerMock->expects($this->exactly(2))->method('run')->willReturn(false);

		$this->stepRunner->run($this->notTwoStep);
	}

	#[Test]
	public function trueOr(): void {

		$this->actionRunnerMock->method('run')->willReturn(true);

		$result = $this->stepRunner->run($this->orTwoStep);

		$this->assertTrue($result['result']);
	}

	#[Test]
	public function trueOrActionFirstActionTrue():void {

		$this->actionRunnerMock->expects($this->once())->method('run')->willReturn(true);

		$this->stepRunner->run($this->orTwoStep);
	}

	#[Test]
	public function trueOrSecondActionTrue():void {

		$this->callCounter = 0;

		$this->actionRunnerMock->expects($this->exactly(2))->method('run')->willReturnCallback(fn() => ++$this->callCounter === 2);

		$this->stepRunner->run($this->orTwoStep);
	}
}