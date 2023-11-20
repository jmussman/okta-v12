<?php

// QueryRunner
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// This class implements running an HTTP GET request and returning the JSON results. The point
// of this class is the use of curl is replaceable with mock during testing.
//

declare(strict_types=1);
namespace Src\Services;

interface QueryRunner {

	public function run(array $headers, string $url) : ?string;
}