<?php


namespace App\Repositories;


use GuzzleHttp\Client;

class GitlabRepository
{
    /**
     * @var String time to start counting closed issues from
     */
    const START_TIME = '2020-03-09T00:00:00';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $baseUrl = "url";

    /**
     * GitlabRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all the gitlab issues from s6-issues.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getAllIssues(): array
    {
        $uri = "/projects/334/issues?labels=ContentID&state=opened&per_page=100&page=1";
        $response = $this->getIssues($uri);
        $issues = json_decode($response->getBody(), true);
        //dd($issues);
        $pages = $response->getHeader('X-Total-Pages')[0];

        for ($page = 2; $pages >= $page; $page++) {
            $uri = "/projects/334/issues?labels=ContentID&state=opened&per_page=100&page=$page";
            $response = $this->getIssues($uri);
            $response = json_decode($response->getBody(), true);
            $issues = array_merge($issues, $response);
        }

        $issues = array_merge($issues, $this->getClosedIssues());

        return $issues;
    }

    /**
     * Get the closed issues from Gitlab
     *
     * @return array
     */
    public function getClosedIssues(): array
    {
        $uri = "/projects/334/issues?labels=ContentID&state=closed&per_page=100&page=1";
        $response = $this->getIssues($uri);
        $issues = json_decode($response->getBody(), true);
        $issues = $this->filterClosedByDate($issues);
        $pages = $response->getHeader('X-Total-Pages')[0];

        for ($page = 2; $pages >= $page; $page++) {
            $uri = "/projects/334/issues?labels=ContentID&state=closed&per_page=100&page=$page";
            $response = $this->getIssues($uri);
            $response = json_decode($response->getBody(), true);
            $response = $this->filterClosedByDate($response);
            $issues = array_merge($issues, $response);
        }

        return $issues;
    }

    /**
     * Filter all closed issues that were closed after a certain date
     * Gitlab doens't provide functionality to filter results by when they
     * were closed.
     *
     * @param array $issues
     * @return array
     */
    private function filterClosedByDate(array $issues): array
    {
        $validIssues = [];
        foreach ($issues as $issue) {
            if (strtotime($issue['closed_at']) > strtotime(self::START_TIME)) {
                $validIssues[] = $issue;
            }
        }

        return $validIssues;
    }

    /**
     * Do the request to get a subset of issues, GitLab paginates them, max
     * of 100 per time.
     *
     * @param string $uri
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function getIssues(string $uri)
    {
        $url = $this->baseUrl . $uri;
        $url = htmlspecialchars($url);

        $response = $this->client->get($url, ['headers' => [
            'PRIVATE-TOKEN' => ''
        ]]);

        return $response;
    }
}