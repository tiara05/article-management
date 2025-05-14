<?php

require 'TaskScheduler.php';

$scheduler = new TaskScheduler();

try {
    $scheduler->addTask("Task6", "Backup database", 1, strtotime("-1 minutes"));
    $scheduler->addTask("Task7", "Run report", 2, strtotime("-1 minutes"), ["Task6"]);

    $task = $scheduler->getNextExecutableTask(time());
    
    if ($task) {
        echo "Next task to execute: {$task['id']} - {$task['description']}\n" . PHP_EOL;
    } else {
        echo "No executable task now.\n" . PHP_EOL;
    }

    echo "Marking Task6 as complete...\n" . PHP_EOL;
    $scheduler->markTaskComplete("Task6");

    $task = $scheduler->getNextExecutableTask(time());
    if ($task) {
        echo "Next task to execute after Task6 completed: {$task['id']} - {$task['description']}\n" . PHP_EOL;
    } else {
        echo "No executable task now.\n" . PHP_EOL;
    }

    echo "Rescheduling Task7...\n" . PHP_EOL;
    $scheduler->rescheduleTask("Task7", strtotime("+1 minutes"));

    $task = $scheduler->getNextExecutableTask(time());
    if ($task) {
        echo "Next task to execute after reschedule: {$task['id']} - {$task['description']}\n" . PHP_EOL;
    } else {
        echo "No executable task now.\n" . PHP_EOL;
    }

    echo "Task Stats:\n" . PHP_EOL;
    print_r($scheduler->getTaskStats());

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
