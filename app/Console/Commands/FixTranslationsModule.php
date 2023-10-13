<?php

namespace App\Console\Commands;

use App\Models\Translation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixTranslationsModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:translation-module {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();

        try {

            $this->fixSourceAndModuleColumns();

            $this->replaceBookingTranslationsToTerms();

            $this->replaceFormBuilderTranslationsToTerms();

            $this->replaceSpaceTranslationsToTerms();

            if ($this->option('rollback')) {

                throw new Exception('rollback option is true');

            } elseif ($this->confirm('Do you wish to commit the changes?')) {

                DB::commit();

                $this->info('committed');
            }

        } catch (\Throwable $th) {

            $this->error($th->getMessage());

            DB::rollBack();

            $this->info('rolled-back');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function fixSourceAndModuleColumns()
    {
        $translations = Translation::where("source", 'LIKE', 'resources/lang/modules/%')
            ->get(['id', 'source', 'module']);

        $outputs = collect();

        $translations->each(function ($translation) use ($outputs) {
            $output = [
                'id' => $translation->id,
                'source' => $translation->source,
            ];

            $moduleName = Str::after($translation->source, 'resources/lang/modules/');
            $moduleName = Str::before($moduleName, '/');

            if ($moduleName == 'formbuilder') {
                $newModuleName = 'form_builder';
            } else {
                $newModuleName = Str::snake($moduleName);
            }

            $translation->source = Str::replace($moduleName, $newModuleName, $translation->source);
            $translation->module = $newModuleName;

            $output['module'] = $newModuleName;
            $output['new_source'] = $translation->source;

            $translation->save();

            $outputs->push($output);
        });

        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['ID', 'Source', 'Module', 'New Source'],
                $outputs->toArray()
            );
        }
    }

    private function replaceTranslationsToTerms(array $replacements, string $module)
    {
        $outputs = collect();

        foreach ($replacements as $key => $replacement) {

            $translations = Translation::where('key', $key)
                ->where('module', $module)
                ->whereNull('group')
                ->where('source', 'LIKE', '%.json')
                ->get(['id', 'key', 'value', 'module', 'group', 'locale', 'source']);

            foreach ($translations as $translation) {
                $output = [
                    'id' => $translation->id,
                    'key' => $translation->key,
                    'source' => $translation->source,
                ];

                $translation->update([
                    'group' => $translation->module.'_term',
                    'key' => $replacement['key'],
                    'value' => Str::lower($translation->value ?? $replacement['value']),
                    'source' => Str::replaceLast('.json', '/terms.php', $translation->source),
                ]);

                $output['r_group'] = $translation->group;
                $output['r_key'] = $translation->key;
                $output['r_value'] = $translation->value;
                $output['r_source'] = $translation->source;

                $outputs->push($output);
            }
        }

        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['ID', 'Key', 'Source', 'R.Group', 'R.Key', 'R.Value', 'R.Source'],
                $outputs->toArray()
            );
        }
    }

    private function deleteTranslationsThatContainTerms(array $translations, string $module)
    {
        Translation::where('module', $module)
            ->whereIn('key', $translations)
            ->delete();
    }

    private function replaceBookingTranslationsToTerms()
    {
        $replacements = [
            "Booking" => [
                'key' => 'booking',
                'value' => 'booking',
            ],
            "Bookings" => [
                'key' => 'bookings',
                'value' => 'bookings',
            ],
            "Event" => [
                'key' => 'event',
                'value' => 'event',
            ],
            "Events" => [
                'key' => 'events',
                'value' => 'events',
            ],
            "Product" => [
                'key' => 'product',
                'value' => 'product',
            ],
            "Products" => [
                'key' => 'products',
                'value' => 'products',
            ],
        ];

        $unusedTranslations = [
            "Latest bookings",
            "New event",
            "Cancel event",
            "Booking settings",
            "New booking",
            "Booking remainder",
            "Booking cancellation",
            "Choose product manager",
            "Book a product",
            "Upcoming events",
            "Last events",
            "Upcoming Events",
            "Product items managed by booking module will be set to draft.",
            "Any upcoming and ongoing booked product items will be canceled.",
            "Users who are assigned as managers will be unassigned from product.",
            "The event has been canceled!",
            "Reschedule event",
            "The event has been rescheduled!",
            "Event booking",
            "Booking event confirmation",
        ];

        $module = 'booking';

        $this->replaceTranslationsToTerms($replacements, $module);

        $this->deleteTranslationsThatContainTerms($unusedTranslations, $module);
    }

    private function replaceFormBuilderTranslationsToTerms()
    {
        $replacements = [
            "Form entries" => [
                'key' => 'form_entries',
                'value' => 'form entries',
            ],
            "Form builder" => [
                'key' => 'form_builder',
                'value' => 'form builder',
            ],
            "Entries" => [
                'key' => 'entries',
                'value' => 'entries',
            ],
            "Field group" => [
                'key' => 'field_group',
                'value' => 'field group',
            ],
            "Form" => [
                'key' => 'form',
                'value' => 'form',
            ],
            "Entry" => [
                'key' => 'entry',
                'value' => 'entry',
            ],
        ];

        $unusedTranslations = [
            "Thank you for filling out the form.",
            "Entry ID",
            "Form ID",
        ];

        $module = 'form_builder';

        $this->replaceTranslationsToTerms($replacements, $module);

        $this->deleteTranslationsThatContainTerms($unusedTranslations, $module);
    }

    private function replaceSpaceTranslationsToTerms()
    {
        $replacements = [
            "Space" => [
                'key' => 'space',
                'value' => 'space',
            ],
            "Spaces" => [
                'key' => 'spaces',
                'value' => 'spaces',
            ],
        ];

        $unusedTranslations = [
            "Space setting",
            "Space type",
            "Users who are assigned as managers will be unassigned from space module.",
            "Pages managed by space will be set to draft.",
            "Events managed by space will be set to draft.",
        ];

        $module = 'space';

        $this->replaceTranslationsToTerms($replacements, $module);

        $this->deleteTranslationsThatContainTerms($unusedTranslations, $module);
    }
}
