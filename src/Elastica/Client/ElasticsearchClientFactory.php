<?php

namespace Valantic\ElasticaBridgeBundle\Elastica\Client;

class ElasticsearchClientFactory
{
    public static function createElasticsearchClient(string $host, int $port): ElasticsearchClient
    {
        return new ElasticsearchClient([
            'host' => $host,
            'port' => $port,
        ]);
    }
}
