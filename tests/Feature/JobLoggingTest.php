<?php

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event as SchedulingEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

function scheduledPurgeTask(): SchedulingEvent
{
    return collect(app(Schedule::class)->events())
        ->first(fn (SchedulingEvent $task): bool => str_contains((string) $task->command, 'app:purge-archived-threads'));
}

it('logs the start and completion of a queued job in the jobs channel', function () {
    $logger = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->with('jobs')->andReturn($logger);

    $logger->shouldReceive('info')->once()->with('Job started', Mockery::type('array'));
    $logger->shouldReceive('info')->once()->with('Job processed', Mockery::on(
        fn (array $context): bool => is_int($context['duration_ms']) && $context['attempt'] === 1,
    ));

    dispatch(function (): void {});
});

it('logs failed jobs without leaking the exception message', function () {
    $logger = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->with('jobs')->andReturn($logger);

    $logger->shouldReceive('info')->once()->with('Job started', Mockery::type('array'));
    $logger->shouldReceive('error')->once()->with('Job failed', Mockery::on(
        fn (array $context): bool => $context['exception'] === RuntimeException::class
            && ! str_contains(json_encode($context), 'jean.dupont@example.com'),
    ));

    dispatch(function (): void {
        throw new RuntimeException('Mailbox unavailable for jean.dupont@example.com');
    });
})->throws(RuntimeException::class);

it('logs the scheduled purge when it runs successfully', function () {
    $logger = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->with('jobs')->andReturn($logger);

    $task = scheduledPurgeTask();
    $task->exitCode = 0;

    $logger->shouldReceive('info')->once()->with('Scheduled task started', ['task' => 'app:purge-archived-threads']);
    $logger->shouldReceive('info')->once()->with('Scheduled task finished', [
        'task' => 'app:purge-archived-threads',
        'duration_ms' => 1250,
    ]);

    event(new ScheduledTaskStarting($task));
    event(new ScheduledTaskFinished($task, 1.25));
});

it('logs the scheduled purge as failed only once when it exits with an error', function () {
    $logger = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->with('jobs')->andReturn($logger);

    $task = scheduledPurgeTask();
    $task->exitCode = 1;

    $logger->shouldNotReceive('info');
    $logger->shouldReceive('error')->once()->with('Scheduled task failed', [
        'task' => 'app:purge-archived-threads',
        'exit_code' => 1,
        'exception' => Exception::class,
    ]);

    event(new ScheduledTaskFinished($task, 0.5));
    event(new ScheduledTaskFailed($task, new Exception('failed with exit code [1]')));
});
