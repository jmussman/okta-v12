<?php

// ActionRunnerTest
// Copyriht © 2023 Joel A Mussman. All rights reserved.
//
// NOTE: PHPUnit freezes the results on 'willResult' in mock objects so you can't change the underlying data
// returned, and does not allow you to replace a mocked method. It is possible to use an anonymous function
// with willReturnCallback but then the "expects" changes and we can't fix that. So, it's easier to just
// set up the mock functionality in each test, although kind of yucky. Other frameworks won't let you set
// up the mock functionality in setup for various other reasons, like it is detected as "not used", so this
// is equivalent.
//

declare(strict_types=1);
namespace Src\Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use \InvalidArgumentException;
use \OutOfBoundsException;
use \TypeError;
use \UnexpectedValueException;

use Src\Services\ActionRunner;
use Src\Services\Cache;
use Src\Services\QueryBuilder;
use Src\Services\QueryRunner;

final class ActionRunnerTest extends TestCase {

	private string $action;
	private ActionRunner $actionRunner;
	private string $apikey;
	private Cache $cacheMock;
	private string $cachePath;
	private array $component;
	private array $headers;
	private string $id;
	private string $jsonResult;
	private string $path;
	private QueryBuilder $queryBuilderMock;
	private array $queryResults;
	private QueryRunner $queryRunnerMock;
	private string $queryString;
	private string $target;
	private string $url;

	#[Before]
	protected function setup(): void {

		$this->apikey = '00-apikey-placeholder';

		$this->cachePath = 'cache.remoteContractorsGroupRule.id';

		$this->component = [ 'api' => '/groups/rules', 'search' => 'costCenter', 'cache.remoteContractorsGroupRule.id' => 'id' ];

		$this->headers = [];
		$this->headers[] = 'Content-Length: 0';
		$this->headers[] = 'Content-Type: application/json';
		$this->headers[] = 'Accept: application/json';
		$this->headers[] = 'Authorization: SSWS ' . $this->apikey;

		$this->id = '0pr60omd1cbYVNNdd0x7';

		$this->jsonResult = '[ { "id": "' . $this->id . '" } ]';

		$this->path = 'https://okta-v12.okta.com/oauth2/v1/groups/rules?search=costCenter';

		$this->queryResults = [[ 'id' => $this->id ]];

		$this->queryString = 'search=costCenter';

		$this->target = 'id';

		$this->url = 'https://okta-v12.okta.com';

		// Mocks.

		$this->cacheMock = $this->createMock(Cache::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->queryRunnerMock = $this->createMock(QueryRunner::class);

		// Class under test.

		$this->actionRunner = new ActionRunner($this->url, $this->apikey, $this->cacheMock, $this->queryBuilderMock, $this->queryRunnerMock);
	}

	#[Test]
	public function failCacheAdd() {

		$this->expectException(OutOfBoundsException::class);

		$this->cacheMock->method('add')->with($this->cachePath, $this->target, $this->queryResults)->willReturn(false);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $this->path ]);
		$this->queryRunnerMock->method('run')->with($this->headers, $this->path)->willReturn($this->jsonResult);

		$this->actionRunner->run($this->component);
	}

	#[Test]
	public function falseOnObjectQueryNotFound() {

		$jsonResult = '[]';

		$this->cacheMock->method('add')->with($this->cachePath, $this->target, $this->queryResults)->willReturn(true);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $this->path ]);
		$this->queryRunnerMock->method('run')->with($this->headers, $this->path)->willReturn($jsonResult);

		$this->assertFalse($this->actionRunner->run($this->component));	
	}

	#[Test]
	public function falseOnObjectQueryError() {

		$jsonResult = '{}';

		$this->cacheMock->method('add')->with($this->cachePath, $this->target, $this->queryResults)->willReturn(true);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $this->path ]);
		$this->queryRunnerMock->method('run')->with($this->headers, $this->path)->willReturn($jsonResult);

		$this->assertFalse($this->actionRunner->run($this->component));	
	}

	#[Test]
	public function findObject() {

		$this->cacheMock->method('add')->with($this->cachePath, $this->target, $this->queryResults)->willReturn(true);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $this->path ]);
		$this->queryRunnerMock->method('run')->with($this->headers, $this->path)->willReturn($this->jsonResult);

		$this->assertTrue($this->actionRunner->run($this->component));
	}

	#[Test]
	public function logEntryCacheTargetReplaced() {

		$cacheTargetJohnSmithId = 'cache.johnSmith.id';
		$jsonResult = '[ { "target": [ { "id": "' . $this->id . '" } ]}]';
		$path = 'eventType eq "user.account.update_profile" and target.id eq "' . $cacheTargetJohnSmithId .'"';
		$pathCompleted = 'https://okta-v12.okta.com/oauth2/v1/logs?filter=eventType eq "user.account.update_profile" and target.id eq "' . $this->id . '"';
		$component = [ 'filter' => $path ];

		$this->cacheMock->method('resolve')->with($cacheTargetJohnSmithId)->willReturn($this->id);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $pathCompleted ]);
		$this->queryRunnerMock->expects($this->once())->method('run')->with($this->headers, $pathCompleted)->willReturn($jsonResult);

		$this->actionRunner->run($component);
	}

	#[Test]
	public function logEntryMultipleCacheTargetsReplaced() {

		$cacheTargetJohnSmithId = 'cache.johnSmith.id';
		$cacheTargetRemoteContractorsGroupRuleId = 'cache.remoteContractorsGroupRule.id';
		$jsonResult = '[ { "target": [ { "id": "' . $this->id . '" } ]}]';
		$remoteContractorsGroupRuleId = '0pr60omd1cbYVNNdd0x7';
		$path = 'eventType eq "group.user_membership.add" and debugContext.debugData.triggeredByGroupRuleId eq "' . $cacheTargetRemoteContractorsGroupRuleId . '" and target.id eq "' . $cacheTargetJohnSmithId . '"';
		$pathCompleted = 'https://okta-v12.okta.com/oauth2/v1/logs?filter=group.user_membership.add" and debugContext.debugData.triggeredByGroupRuleId eq "' . $remoteContractorsGroupRuleId . '" and target.id eq "' . $this->id .'"';
		$component = [ 'filter' => $path ];

		$this->cacheMock->method('resolve')->willReturnCallback(fn(string $target): string => ($target == $cacheTargetJohnSmithId) ? $this->id : $remoteContractorsGroupRuleId);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $pathCompleted ]);
		$this->queryRunnerMock->expects($this->once())->method('run')->with($this->headers, $pathCompleted)->willReturn($jsonResult);

		$this->actionRunner->run($component);
	}

	#[Test]
	public function rejectApiKeyAndUrlEmpty() {

		$this->expectException(InvalidArgumentException::class);

		new ActionRunner('', '', $this->cacheMock, $this->queryBuilderMock, $this->queryRunnerMock);
	}

	#[Test]
	public function rejectApiKeyEmpty() {

		$this->expectException(InvalidArgumentException::class);

		new ActionRunner($this->url, '', $this->cacheMock, $this->queryBuilderMock, $this->queryRunnerMock);
	}

	#[Test]
	public function rejectApiKeyInvalidParameter(): void {

		$this->expectException(TypeError::class);

		new ActionRunner($this->url, 0, $this->cacheMock, $this->queryBuilderMock, $this->queryRunnerMock);
	}

	#[Test]
	public function rejectCacheInvalidParameter() {

		$this->expectException(TypeError::class);

		new ActionRunner($this->url, $this->apikey, 0, $this->queryBuilderMock, $this->queryRunnerMock);
	}

	#[Test]
	public function rejectCacheTargetResolveNull() {

		$cacheTargetJohnSmithId = 'cache.johnSmith.id';
		$jsonResult = '[ { "target": [ { "id": "' . $this->id . '" } ]}]';
		$path = 'eventType eq "user.account.update_profile" and target.id eq "' . $cacheTargetJohnSmithId .'"';
		$pathCompleted = 'https://okta-v12.okta.com/oauth2/v1/logs?filter=eventType eq "user.account.update_profile" and target.id eq "' . $this->id . '"';
		$component = [ 'filter' => $path ];

		$this->expectException(UnexpectedValueException::class);

		$this->cacheMock->method('resolve')->with($cacheTargetJohnSmithId)->willThrowException(new UnexpectedValueException());
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $pathCompleted ]);
		$this->queryRunnerMock->method('run')->with($this->headers, $pathCompleted)->willReturn($jsonResult);

		$this->actionRunner->run($component);
	}

	#[Test]
	public function rejectComponentInvalidParameter() : void {

		$this->expectException(TypeError::class);

		$this->actionRunner->run(0);
	}

	#[Test]
	public function rejectLogEntryCacheActionMissingWordTarget() {

		$component = [ 'eventType' => 'user.lifecycle.create', 'cache.johnSmith.id' => 'id' ];
		$jsonResult = '[ { "target": [ { "id": "' . $this->id . '" } ]}]';

		$this->expectException(InvalidArgumentException::class);

		$this->cacheMock->method('add')->with($this->cachePath, $this->target, $this->queryResults)->willReturn(true);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $this->path ]);
		$this->queryRunnerMock->method('run')->with($this->headers, $this->path)->willReturn($jsonResult);

		$this->assertFalse($this->actionRunner->run($component));	
	}

	#[Test]
	public function rejectUrlEmpty() {

		$this->expectException(InvalidArgumentException::class);

		new ActionRunner('', $this->apikey, $this->cacheMock, $this->queryBuilderMock, $this->queryRunnerMock);
	}

	#[Test]
	public function rejectUrlInvalidParameter(): void {

		$this->expectException(TypeError::class);

		new ActionRunner(0, $this->apikey, $this->cacheMock, $this->queryBuilderMock, $this->queryRunnerMock);
	}

	#[Test]
	public function successCacheAdd() {

		$this->cacheMock->expects($this->once())->method('add')->with($this->cachePath, $this->target, $this->queryResults)->willReturn(true);
		$this->queryBuilderMock->method('build')->willReturn([ 'headers' => $this->headers, 'path' => $this->path ]);
		$this->queryRunnerMock->method('run')->with($this->headers, $this->path)->willReturn($this->jsonResult);

		$this->actionRunner->run($this->component);
	}

}