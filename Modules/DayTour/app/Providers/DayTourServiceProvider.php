<?php

namespace Modules\DayTour\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\DayTour\Actions\CreateDayTourAction;
use Modules\DayTour\Actions\UpdateDayTourAction;
use Modules\DayTour\Actions\UploadDayTourImageAction;
use Modules\DayTour\Repositories\DayTourRepository;
use Modules\DayTour\Services\S3ImageService;
use Modules\DayTour\Services\DayTourCacheService;

class DayTourServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'DayTour';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'daytour';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->registerBindings();
    }

    /**
     * Register service bindings
     */
    protected function registerBindings(): void
    {
        $this->app->singleton(DayTourRepository::class);
        $this->app->singleton(S3ImageService::class);
        $this->app->singleton(DayTourCacheService::class);

        $this->app->bind(CreateDayTourAction::class, function ($app) {
            return new CreateDayTourAction($app->make(DayTourCacheService::class));
        });

        $this->app->bind(UpdateDayTourAction::class, function ($app) {
            return new UpdateDayTourAction($app->make(DayTourCacheService::class));
        });

        $this->app->bind(UploadDayTourImageAction::class, function ($app) {
            return new UploadDayTourImageAction($app->make(S3ImageService::class));
        });
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
