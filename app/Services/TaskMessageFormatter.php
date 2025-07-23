<?php

namespace App\Services;

class TaskMessageFormatter
{
    public static function format(array $tasks): string
    {
        $message = "📋 *Список новых задач:*\n\n";

        foreach ($tasks as $task) {
            $message .= "👤 Пользователь #{$task->userId} — {$task->title}\n";
        }

        return $message;
    }
}
