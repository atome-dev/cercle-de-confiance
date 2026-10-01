<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event as SchedulingEvent;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureJobLogging();
        $this->configureLoginTracking();
    }

    /**
     * Record every successful login, including "remember me" ones, for the administrators' members page.
     */
    protected function configureLoginTracking(): void
    {
        Event::listen(function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->logins()->create(['logged_in_at' => now()]);
            }
        });
    }

    /**
     * Trace each queued job's and scheduled task's lifecycle in the "jobs" log channel.
     *
     * Only the job class, identifiers and timings are logged — never the
     * payload nor the exception message, which may carry a sender's email.
     */
    protected function configureJobLogging(): void
    {
        /** @var array<string, float> $startedAt */
        $startedAt = [];

        Event::listen(function (JobProcessing $event) use (&$startedAt): void {
            $startedAt[$event->job->getJobId()] = microtime(true);

            Log::channel('jobs')->info('Job started', $this->jobContext($event->job));
        });

        Event::listen(function (JobProcessed $event) use (&$startedAt): void {
            Log::channel('jobs')->info('Job processed', [
                ...$this->jobContext($event->job),
                'duration_ms' => $this->elapsedMilliseconds($startedAt, $event->job),
            ]);
        });

        Event::listen(function (JobFailed $event) use (&$startedAt): void {
            Log::channel('jobs')->error('Job failed', [
                ...$this->jobContext($event->job),
                'duration_ms' => $this->elapsedMilliseconds($startedAt, $event->job),
                'exception' => $event->exception::class,
            ]);
        });

        Event::listen(function (ScheduledTaskStarting $event): void {
            Log::channel('jobs')->info('Scheduled task started', ['task' => $this->scheduledTaskName($event->task)]);
        });

        Event::listen(function (ScheduledTaskFinished $event): void {
            // A non-zero exit code is followed by ScheduledTaskFailed, which logs it.
            if (($event->task->exitCode ?? 0) !== 0) {
                return;
            }

            Log::channel('jobs')->info('Scheduled task finished', [
                'task' => $this->scheduledTaskName($event->task),
                'duration_ms' => (int) round($event->runtime * 1000),
            ]);
        });

        Event::listen(function (ScheduledTaskFailed $event): void {
            Log::channel('jobs')->error('Scheduled task failed', [
                'task' => $this->scheduledTaskName($event->task),
                'exit_code' => $event->task->exitCode,
                'exception' => $event->exception::class,
            ]);
        });
    }

    /**
     * Name a scheduled task by its Artisan command, without the PHP binary path.
     */
    protected function scheduledTaskName(SchedulingEvent $task): string
    {
        if ($task->description) {
            return $task->description;
        }

        if ($task->command === null) {
            return $task->getSummaryForDisplay();
        }

        return Str::of($task->command)->after('artisan')->trim(" '")->toString();
    }

    /**
     * @return array{job: string, id: string|int|null, connection: string, queue: string, attempt: int}
     */
    protected function jobContext(Job $job): array
    {
        return [
            'job' => $job->resolveName(),
            'id' => $job->uuid() ?? $job->getJobId(),
            'connection' => $job->getConnectionName(),
            'queue' => $job->getQueue(),
            'attempt' => $job->attempts(),
        ];
    }

    /**
     * @param  array<string, float>  $startedAt
     */
    protected function elapsedMilliseconds(array &$startedAt, Job $job): ?int
    {
        $start = $startedAt[$job->getJobId()] ?? null;
        unset($startedAt[$job->getJobId()]);

        return $start === null ? null : (int) round((microtime(true) - $start) * 1000);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
