<?php
namespace Judge;

use GuzzleHttp\Client;
use InvalidArgumentException;

class Judge
{
    // default timeout
    const TIMEOUT = 2;
    // default connection timeout
    const CONNECTION_TIMEOUT = 2;
    /**
     * Judge api base uri
     */
    protected $base;
    /**
     * Account ID
     */
    protected $id;
    /**
     * Account Secret
     */
    protected $secret;
    /**
     * Guzzle client
     */
    protected $client;

    /**
     * Constructor
     * @param $base {string} judge api base uri
     * @param $id {string} account id
     * @param $secret {string} account secret
     */
    public function __construct($base, $id, $secret)
    {
        // check the base is a valid uri
        if (preg_match('/^https?:\/\/(-\.)?([^\s\/?\.#-]+\.?)+(\/[^\s]*)?$/i', $base) === 0) {
            throw new InvalidArgumentException('Invalid base uri');
        }
        $this->base = $base;
        $this->id = $id;
        $this->secret = $secret;
        // create guzzle http client
        $this->client = new Client([
            'base_uri' => $base,
            'timeout' => self::TIMEOUT,
            'connection_timeout' => self::CONNECTION_TIMEOUT,
        ]);
    }
    /**
     * Generate sigature
     * @param $path {string} resource path
     * @param $method {GET|POST|PUT|DELETE|...} http method
     */
    protected function getAuthorization($path, $method)
    {
        // current unix timestamp
        $current = time();
        $sigature = hash_hmac('sha256', $this->id . $current . $path . $method, $this->secret);
        return $this->id . ' ' . $current . ' ' . $path . ' ' . $method . ' ' . $sigature;
    }
    /**
     * Create a new problem
     * @param $problem {object} problem will be created
     */
    public function addProblem($problem)
    {
        $path = '/problem';

        $response = $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'POST'),
            ],
            'json' => $problem,
        ]);

        $result = json_decode($response->getBody());
        $result->statusCode = $response->getStatusCode();

        return $result;
    }
    /**
     * Delete a problem
     * @param $problemId {object} the problem want to delete
     */
    public function removeProblem($problem)
    {
        $path = '/problem';

        $response = $this->client->delete($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'DELETE'),
            ],
            'json' => $problem,
        ]);

        $result = json_decode($response->getBody());
        $result->statusCode = $response->getStatusCode();

        return $result;
    }
    /**
     * Add judge record
     * @param $record {object} the code want to be judged
     */
    public function add($record)
    {
        $path = '/status';

        $response = $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'POST'),
            ],
            'json' => $record,
        ]);

        $result = json_decode($response->getBody());
        $result->statusCode = $response->getStatusCode();

        return $result;
    }
    /**
     * Query judge record
     */
    public function query($record)
    {
        $path = '/status';

        $response = $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'GET'),
            ],
            'query' => $record,
        ]);

        $result = json_decode($response->getBody());
        $result->statusCode = $response->getStatusCode();

        return $result;
    }
}
