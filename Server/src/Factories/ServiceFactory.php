<?php

// ServiceFactory
// Copyright © Joel A Mussman. All rights reserved.
//
// This factory is leveraged by the index.php startup to create the chain of
// service objects required for production. These are injected at the
// creation of the controller, in lieu of a formal DI framework (simpler).
//

declare(strict_types=1);
namespace Src\Factories;

use Src\Services\ActionRunner;
use Src\Services\Cache;
use Src\Services\CurlQueryRunner;
use Src\Services\PartRunner;
use Src\Services\QueryBuilder;
use Src\Services\SchemaRunner;
use Src\Services\StepRunner;
use Src\Services\VerifierService;

class ServiceFactory {

	public static function build(string $url, string $apikey): VerifierService {

		$cache = new Cache();
		$queryBuilder = new QueryBuilder();
		$queryRunner = new CurlQueryRunner();
		$actionRunner = new ActionRunner($url, $apikey, $cache, $queryBuilder, $queryRunner);
		$stepRunner = new StepRunner($actionRunner);
		$partRunner = new PartRunner($stepRunner);
		$schemaRunner = new SchemaRunner($partRunner);
		$verifierService = new VerifierService($schemaRunner);

		return $verifierService;
	}
}