<?php

// VerifyController
// Copyright © Joel A Mussman. All rights reserved.
//

declare(strict_types=1);
namespace Src\Controllers;

use Src\Services\VerifierService;

class VerifyController
{

	private ?VerifierService $verifierService;

	public function __construct(VerifierService $verifierService = NULL) {

		$this->verifierService = $verifierService;
	}

    public function index($uri)
    {

		if (!$this->verifierservice) {

			// This is called in production so use the factory to build the service with the API token or
			// credentials we are given.

			$this->verifierService = ServiceFactory::build($url, $apikey);			// We need to build this at invokation time, not before.
		}

        $report = [];

        $this->respondOK($report);
    }

    private function respondOK($data)
    {
        header('HTTP/1.1 200 OK');
		header("Content-Type: application/json; charset=UTF-8");

        echo json_encode($data);
    }
}