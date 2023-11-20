<?php declare(strict_types=1);

// CacheTest
// Copyriht © 2023 Joel A Mussman. All rights reserved.
//

namespace Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use \InvalidArgumentException;
use \TypeError;
use \UnexpectedValueException;

use Src\Services\Cache;

final class CacheTest extends TestCase {

	private $cache;
	private $key;
	private $path;
	private $source;

	#[Before]
	protected function setup(): void {

		$this->cache = new Cache();
		$this->key = 'cache.groupRule.id';
		$this->path = 'id';
		$this->source = [[ 'id' => '0pr60omd1cbYVNNdd0x7' ]];
	}

	#[Test]
	public function addPropertyInMultipleTargets(): void {

		$this->source = [[ 'something' => 'something else' ], [ 'id' => '0pr60omd1cbYVNNdd0x7' ]];

		$result = $this->cache->add($this->key, $this->path, $this->source);
		$result = $this->cache->resolve($this->key);

		$this->assertEquals($this->source[1]['id'], $result);
	}

	#[Test]
	public function falseForAddMissingSourceProperty(): void {

		$this->path = 'ruleId'; 

		$result = $this->cache->add($this->key, $this->path, $this->source);

		$this->assertFalse($result);
	}

	#[Test]
	public function rejectResolveUnknownProperty(): void {

		$this->expectException(UnexpectedValueException::class);

		$result = $this->cache->add($this->key, $this->path, $this->source);
		$result = $this->cache->resolve('cache.groupRule.ruleId');
	}

	#[Test]
	public function insertAndRetrieveCacheProperty(): void {

		$result = $this->cache->add($this->key, $this->path, $this->source);
		$result = $this->cache->resolve($this->key);

		$this->assertEquals($this->source[0]['id'], $result);
	}

	#[Test]
	public function rejectAddEmptyKey(): void {

		$this->expectException(InvalidArgumentException::class);

		$result = $this->cache->add('', $this->path, $this->source);
	}

	#[Test]
	public function rejectAddEmptyPath(): void {

		$this->expectException(InvalidArgumentException::class);

		$result = $this->cache->add($this->key, '', $this->source);
	}

	#[Test]
	public function rejectAddEmptySource(): void {

		$this->expectException(InvalidArgumentException::class);

		$result = $this->cache->add($this->key, $this->path, []);
	}

	#[Test]
	public function rejectAddEmptyKeyAndPath(): void {

		$this->expectException(InvalidArgumentException::class);

		$result = $this->cache->add('', '', $this->source);
	}

	#[Test]
	public function rejectAddEmptyPathAndSource(): void {

		$this->expectException(InvalidArgumentException::class);

		$result = $this->cache->add($this->key, '', []);
	}

	#[Test]
	public function rejectAddEmptyKeyPathAndSource(): void {

		$this->expectException(InvalidArgumentException::class);

		$result = $this->cache->add('', '', []);
	}

	#[Test]
	public function rejectAddInvalidKeyParameter(): void {

		// This test ensures that strict type is enabled and $api is marked.

		$this->expectException(TypeError::class);

		$result = $this->cache->add(0, $this->path, $this->source);
	}

	#[Test]
	public function rejectAddInvalidPathParameter(): void {

		// This test ensures that strict type is enabled and $api is marked.

		$this->expectException(TypeError::class);

		$result = $this->cache->add($this->key, 0, $this->source);
	}

	#[Test]
	public function rejectAddInvalidSourceParameter(): void {

		// This test ensures that strict type is enabled and $api is marked.

		$this->expectException(TypeError::class);

		$result = $this->cache->add($this->key, $this->path, 0);
	}

	#[Test]
	public function rejectResolveEmptyPath() {

		$this->expectException(InvalidArgumentException::class);

		$result = $this->cache->add($this->key, $this->path, $this->source);
		$result = $this->cache->resolve('');
	}
}