<?php

// CacheTest
// Copyriht © 2023 Joel A Mussman. All rights reserved.
//

declare(strict_types=1);
namespace Services;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use \InvalidArgumentException;
use \TypeError;

use Src\Services\QueryBuilder;

final class QueryBuilderTest extends TestCase {

	private $api;
	private $apikey;
	private $queryBuilder;
	private $queryString;
	private $url;

	#[Before]
	protected function setup(): void {

		$this->api = '/groups/rules';
		$this->apikey = '00-apikey-placeholder';
		$this->queryString = 'search=costCenter';
		$this->url = 'https://okta-v12.okta.com';

		$this->queryBuilder = new QueryBuilder();
	}

	#[Test]
	public function acceptsEmptyQueryString(): void {

		$query = $this->queryBuilder->build($this->url, $this->api, '', $this->apikey);

		$this->assertFalse(strpos($query[QueryBuilder::PATHKEY], '?'));
	}

	#[Test]
	public function apiAddedSecond(): void {

		$query = $this->queryBuilder->build($this->url, $this->api, $this->queryString, $this->apikey);
		
		$this->assertEquals(strlen($this->url) + strlen('/oauth2/v1'), strpos($query[QueryBuilder::PATHKEY], $this->api));
	}

	#[Test]
	public function apikeyAddedToHeaders(): void {

		$query = $this->queryBuilder->build($this->url, $this->api, $this->queryString, $this->apikey);
		
		$this->assertTrue(in_array('Authorization: SSWS ' . $this->apikey, $query[QueryBuilder::HEADERSKEY]));
	}

	#[Test]
	public function rejectEmptyApi(): void {

		$this->expectException(InvalidArgumentException::class);

		$query = $this->queryBuilder->build($this->url, '', $this->queryString, $this->apikey);
	}

	#[Test]
	public function rejectEmptyApiKey(): void {

		$this->expectException(InvalidArgumentException::class);

		$query = $this->queryBuilder->build($this->url, $this->api, $this->queryString, '');
	}

	#[Test]
	public function rejectEmptyUrl(): void {

		$this->expectException(InvalidArgumentException::class);

		$query = $this->queryBuilder->build('', $this->api, $this->queryString, $this->apikey);
	}

	#[Test]
	public function rejectInvalidApiParameter(): void {

		// This test ensures that strict type is enabled and $api is marked.

		$this->expectException(TypeError::class);

		$query = $this->queryBuilder->build($this->url, 0, $this->queryString, $this->apikey);
	}

	#[Test]
	public function rejectInvalidApiKeyParameter(): void {

		// This test ensures that strict type is enabled and $apikey is marked.

		$this->expectException(TypeError::class);

		$query = $this->queryBuilder->build($this->url, $this->api, $this->queryString, 0);
	}

	#[Test]
	public function rejectInvalidQueryStringParameter(): void {

		// This test ensures that strict type is enabled and $queryString is marked.

		$this->expectException(TypeError::class);

		$query = $this->queryBuilder->build($this->url, $this->api, 0, $this->apikey);
	}

	#[Test]
	public function rejectInvalidUrlParameter(): void {

		// This test ensures that strict type is enabled and $url is marked.

		$this->expectException(TypeError::class);

		$query = $this->queryBuilder->build(0, $this->api, $this->queryString, $this->apikey);
	}

	#[Test]
	public function queryStringAddedThird(): void {

		$query = $this->queryBuilder->build($this->url, $this->api, $this->queryString, $this->apikey);
		
		$this->assertEquals(strlen($this->url) + strlen('/oauth2/v1') + strlen($this->api) + 1, strpos($query[QueryBuilder::PATHKEY], $this->queryString));
	}

	#[Test]
	public function urlAddedFirst(): void {

		$query = $this->queryBuilder->build($this->url, $this->api, $this->queryString, $this->apikey);
		
		$this->assertEquals(0, strpos($query[QueryBuilder::PATHKEY], $this->url));
	}
}