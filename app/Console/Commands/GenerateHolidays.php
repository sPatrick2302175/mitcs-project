<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomHoliday;

class GenerateHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:generate {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate standard Philippine regular holidays for a given year (defaults to current year).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Use the year passed in the argument, or default to the current year
        $year = $this->argument('year') ?? date('Y');
        
        $this->info("Generating regular holidays for the year {$year}...");

        $easterDays = easter_days($year);
        $easter = new \DateTime("$year-03-21");
        $easter->modify("+$easterDays days");

        $maundyThursday = clone $easter;
        $maundyThursday->modify('-3 days');

        $goodFriday = clone $easter;
        $goodFriday->modify('-2 days');

        $nationalHeroesDay = new \DateTime("last monday of august $year");

        $regularHolidays = [
            ['name' => "New Year's Day", 'date' => "$year-01-01"],
            ['name' => "Araw ng Kagitingan", 'date' => "$year-04-09"],
            ['name' => "Maundy Thursday", 'date' => $maundyThursday->format('Y-m-d')],
            ['name' => "Good Friday", 'date' => $goodFriday->format('Y-m-d')],
            ['name' => "Labor Day", 'date' => "$year-05-01"],
            ['name' => "Independence Day", 'date' => "$year-06-12"],
            ['name' => "National Heroes Day", 'date' => $nationalHeroesDay->format('Y-m-d')],
            ['name' => "Bonifacio Day", 'date' => "$year-11-30"],
            ['name' => "Christmas Day", 'date' => "$year-12-25"],
            ['name' => "Rizal Day", 'date' => "$year-12-30"],
        ];

        $count = 0;

        foreach ($regularHolidays as $holiday) {
            $exists = CustomHoliday::where('name', $holiday['name'])
                ->whereYear('date', $year)
                ->exists();

            if (!$exists) {
                CustomHoliday::create([
                    'date' => $holiday['date'], 
                    'name' => $holiday['name'], 
                    'type' => 'regular', 
                    'is_half_day' => false
                ]);
                $count++;
            }
        }

        $this->info("Successfully generated {$count} new holidays for {$year}.");
    }
}