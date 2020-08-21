<?php


namespace App\Services;


use App\Repositories\GitlabRepository;
use App\Repositories\KairosDB;

class GitlabService
{
    /**
     * @var array Gitlab column labels
     */
    const LABELS = [
        //'Someday' => ['count' => 0],
        'In Progress' => ['count' => 0],
        'To do' => ['count' => 0],
        'Backlog' => ['count' => 0],
        'To Test on Production' => ['count' => 0],
        'To Test on Staging' => ['count' => 0],
        'Blocked' => ['count' => 0],
        //'Epic' => ['count' => 0],
        'Ready for Review' => ['count' => 0],
        'Waiting on Design' => ['count' => 0],
        'Closed' => ['count' => 0],
    ];

    /**
     * @var String The string to use for 'closed' issues
     */
    const CLOSED_STRING = 'closed';

    /**
     * @var GitlabRepository
     */
    protected $repository;

    /**
     * @var KairosDB
     */
    protected $kairos;

    /**
     * GitlabService constructor.
     * @param GitlabRepository $repository
     */
    public function __construct(GitlabRepository $repository, KairosDB $kairos)
    {
        $this->repository = $repository;
        $this->kairos = $kairos;
    }

    private function buildMetricArray()
    {
        // Build the metric array as a class member.
    }

    /**
     * Get all the issues from gitlab, and process them.
     */
    public function getIssues()
    {
        $issues = $this->repository->getAllIssues();

        $metrics = self::LABELS;
        $count = 0;
        foreach ($issues as $issue) {
            $class = $this->classifyIssue($issue);

            if ($class) {
                $metrics[$class]['count']++;
            }

            $count++;
        }

        $this->uploadMetrics($metrics);
    }

    /**
     * Classify the issues to one of self::LABELS
     *
     * @param array $issue
     * @return int|string
     */
    private function classifyIssue(array $issue)
    {
        if ($issue['state'] === self::CLOSED_STRING) {
            return ucfirst(self::CLOSED_STRING);
        }

        foreach (self::LABELS as $label => $count) {
            if (in_array($label, $issue['labels'])) {
                return $label;
            }
        }
    }

    /**
     * Upload the metrics to KairosDB/Grafana
     *
     * @param array $metrics
     */
    private function uploadMetrics(array $metrics)
    {
        foreach($metrics as $label => $count) {
            $this->kairos->uploadMetric($label, $count['count']);
        }
    }
}