<?php

// CurlQueryRunner
// Copyright © 2023 Joel A Mussman. All rights reserved.
//
// This class implements running an HTTP GET request and returning the JSON results. The point
// of this class is the use of curl is replaceable with mock during testing.
//

namespace Services;

use Services\QueryRunner;

class CurlQueryRunner extends QueryRunner {

	public function run(array $headers, string $url) : ?string {

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPGET, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($curl);

		curl_close($curl);
		return $result;
	}
}