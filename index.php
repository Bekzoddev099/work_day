<?php

declare(strict_types=1);

require_once 'Workday.php';

$workDay = new Workday();

require_once 'view.php';

if (!empty($_POST)) {
    if (isset($_POST['done'])) {
        $result = $workDay->markAsDone((int) $_POST['done']);
        if ($result['status'] == 'failed') {
            echo "<div class='text-center text-danger'>{$result['message']}</div>";
            return;
        } elseif ($result['status'] == 'success') {
            echo "<div class='text-center text-success'>{$result['message']}</div>";
        }
        return;
    }
    $result = $workDay->store($_POST); 
    if ($result['status'] == 'failed') {
        echo "<div class='text-center text-danger'>{$result['message']}</div>";
        return;
    } elseif ($result['status'] == 'success') {
        echo "<div class='text-center text-success'>{$result['message']}</div>";
    }
}
