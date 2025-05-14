<?php

class TaskScheduler {
    private $storageFile = 'tasks.json';
    private $tasks = [];

    public function __construct() {
        if (file_exists($this->storageFile)) {
            $this->tasks = json_decode(file_get_contents($this->storageFile), true);
        }
    }

    private function saveTasks() {
        file_put_contents($this->storageFile, json_encode($this->tasks, JSON_PRETTY_PRINT));
    }

    public function addTask($taskId, $description, $priority, $scheduledTime, $dependencies = [])
    {
        if (isset($this->tasks[$taskId])) {
            throw new Exception("Task with ID '$taskId' already exists. Consider using a different ID.");
        }

        $this->tasks[$taskId] = [
            'id' => $taskId,
            'description' => $description,
            'priority' => $priority,
            'scheduledTime' => $scheduledTime,
            'dependencies' => $dependencies,
            'completed' => false
        ];
    }

    public function getNextExecutableTask($currentTime) {
        $candidates = [];

        foreach ($this->tasks as $id => $task) {
            if ($task['completed']) continue;
            if ($task['scheduledTime'] > $currentTime) continue;

            $depsMet = true;
            foreach ($task['dependencies'] as $depId) {
                if (!isset($this->tasks[$depId]) || !$this->tasks[$depId]['completed']) {
                    $depsMet = false;
                    break;
                }
            }

            if ($depsMet) {
                $candidates[] = array_merge($task, ['id' => $id]);
            }
        }

        if (empty($candidates)) return null;

        usort($candidates, function ($a, $b) {
            return $a['priority'] <=> $b['priority'] ?: $a['scheduledTime'] <=> $b['scheduledTime'];
        });

        return $candidates[0];
    }

    public function markTaskComplete($taskId) {
        if (!isset($this->tasks[$taskId])) {
            throw new Exception("Task with ID '$taskId' not found.");
        }

        $this->tasks[$taskId]['completed'] = true;
        $this->saveTasks();
    }

    public function rescheduleTask($taskId, $newScheduledTime) {
        if (!isset($this->tasks[$taskId])) {
            throw new Exception("Task with ID '$taskId' not found.");
        }

        $this->tasks[$taskId]['scheduledTime'] = $newScheduledTime;
        $this->saveTasks();
    }

    public function getTaskStats() {
        $stats = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        foreach ($this->tasks as $task) {
            if (!$task['completed']) {
                $stats[$task['priority']]++;
            }
        }

        return $stats;
    }
}
