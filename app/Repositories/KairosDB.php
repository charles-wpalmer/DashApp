<?php


namespace App\Repositories;


use GuzzleHttp\Client;

class KairosDB
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $baseUrl = "http://kairosdb:8080/api/v1/datapoints";

    /**
     * GitlabRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Upload the metric and value to KairosDB
     *
     * @param string $metric
     * @param int $value
     */
    public function uploadMetric(string $metric, int $value)
    {
        $metric = str_replace(" ", "_", $metric);
        $data = [
            "name" => "gitlab.issues.$metric",
            "timestamp" => round(microtime(true) * 1000),
            "value" => $value,
            "tags" => ["gitlab" => "issues"]
        ];

        echo "Uploading: " . PHP_EOL;
        dump(\GuzzleHttp\json_encode($data));
        $resp = $this->client->post($this->baseUrl, ['body' => json_encode($data)]);
    }

    /**
     * Function to view a certain metric from KairosDB
     *
     * @param string $metric
     * @return array
     */
    public function getData(string $metric): array
    {
        htmlspecialchars($str = "{start_absolute:1,metrics:[{name:$metric}]}");
        $resp = $this->client->get("http://kairosdb:8080/api/v1/datapoints/query?query=$str");

        return json_decode($resp->getBody(), true);
    }
}
