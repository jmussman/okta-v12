<?php

// VerifyController
// Copyright © Joel A Mussman. All rights reserved.
//

declare(strict_types=1);
namespace Src\Controllers;

use Src\Services\VerifierService;

class VerifyController
{

	private $verifierService;

	public function __construct(VerifierService $verifierService) {

		$this->verifierService = $verifierService;
	}

    public function index($uri)
    {
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