protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
{
    $schedule->command('anomalies:detect')->hourly();
}
