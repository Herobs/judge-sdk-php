<?php
namespace Judge;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Judge\JudgeServiceException;

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
     *
     * @param $base {string} judge api base uri
     * @param $id {string} account id
     * @param $secret {string} account secret
     */
    public function __construct($base, $id, $secret)
    {
        // check the base is a valid uri
        if (preg_match('/^https?:\/\/(-\.)?([^\s\/?\.#-]+\.?)+(\/[^\s]*)?$/i', $base) === 0) {
            throw new JudgeServiceException('Invalid base uri');
        }
        $this->base = $base;
        $this->id = $id;
        $this->secret = $secret;
        // create guzzle http client
        $this->client = new Client([
            'base_uri' => $base,
            'timeout' => self::TIMEOUT,
            'connection_timeout' => self::CONNECTION_TIMEOUT,
            'http_errors' => false,
        ]);
    }

    /**
     * Generate sigature
     *
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
     *
     * @param $problem {object} problem will be created
     * @return {object} raw http response
     */
    protected function addProblem($problem)
    {
        $path = '/problem';

        return $this->client->post($path, [
            'headers' => [
                    'Authorization' => $this->getAuthorization($path, 'POST'),
            ],
            'json' => $problem,
        ]);
    }

    /**
     * Update specific problem
     *
     * @param $problemId {integer} the problem will be updated
     * @param $problem {object} problem want to be updated
     * @return {object} raw http response
     */
    protected function updateProblem($problemId, $problem)
    {
        $path = '/problem/'.$problemId;

        return $this->client->put($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'PUT'),
            ],
            'json' => $problem,
        ]);
    }

    /**
     * Delete specific problem
     *
     * @param $problemId {object} the problem will be deleted
     * @return {object} raw http response
     */
    protected function removeProblem($problemId)
    {
        $path = '/problem/'.$problemId;

        return $this->client->delete($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'DELETE'),
            ],
        ]);
    }

    /**
     * Copy specific problem
     *
     * @param $problemId {object} the problem will be copied
     * @return {object} raw http response
     */
    protected function copyProblem($problemId)
    {
        $path = '/copy/'.$problemId;

        return $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'GET'),
            ],
        ]);
    }

    /**
     * Update specific test file
     *
     * @param $case {object} the test case want to be updated
     * @return {object} raw http response
     */
    protected function testcase($problemId, $caseId, $case)
    {
        $path = '/testcase/'.$problemId.'/'.$caseId;

        return $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'POST'),
            ],
            'json' => $case,
        ]);
    }

    /**
     * Delete specific test case
     *
     * @param $case {object} the case want to be deleted
     * @return {object} raw http response
     */
    protected function removeTestCase($problemId, $caseId)
    {
        $path = '/testcase/'.$problemId.'/'.$caseId;

        return $this->client->delete($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'DELETE'),
            ],
        ]);
    }

    /**
     * Add a judge record
     *
     * @param $record {object} the code want to be judged
     * @return {object} raw http response
     */
    protected function add($record)
    {
        $path = '/status';

        return $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'POST'),
            ],
            'json' => $record,
        ]);
    }

    /**
     * Query judge record
     * @param $record {integer} record status id
     * @return {object} raw http response
     */
    protected function query($statusId)
    {
        $path = '/status/'.$statusId;

        return $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->getAuthorization($path, 'GET'),
            ],
        ]);
    }

    /**
     * Handle dynamic method calls
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return judge service response
     */
    public function __call($method, $parameters)
    {
        try {
            $response = call_user_func_array([$this, $method], $parameters);
            $result = json_decode($response->getBody());
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new JudgeServiceException($result->message);
            }
        } catch (RequestException $e) {
            throw new JudgeServiceException('Cannot establish connection with judge server. '.$e->getMessage());
        } catch (Exception $e) {
            throw new JudgeServiceException('Unknown error. '.$e->getMessage());
        }

        return $result;
    }
}
