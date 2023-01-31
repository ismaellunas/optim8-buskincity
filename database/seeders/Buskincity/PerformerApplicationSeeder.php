<?php

namespace Database\Seeders\Buskincity;

use App\Services\ModuleService;
use App\Models\User;
use App\Models\Page;
use App\Models\Setting;
use App\Models\PageTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\Form;
use Illuminate\Support\Str;

class PerformerApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $name = "Performer Application";

        $formId = null;

        if ($this->isModuleFormBuilderActive()) {
            $form = Form::factory()
                ->has(
                    FieldGroup::factory()
                        ->count(5)
                        ->state(new Sequence(...$this->getFieldGroups()))
                )
                ->create([
                    'key' => Str::of($name)->lower()->snake()->value(),
                    'name' => $name,
                ]);

            $formId = $form->id;
        }

        $adminUser = User::find(1);

        $page = Page::factory()
            ->hasTranslations(1, [
                'title' => $name,
                'slug' => Str::slug($name),
                'status' => PageTranslation::STATUS_PUBLISHED,
                'data' => $this->getDataPageTranslation(),
            ])
            ->for($adminUser, 'author')
            ->create();

        $pageId = $page->id;

        $widgetPerformerApplications = [
            [
                "key" => "page_id",
                "display_name" => null,
                "value" => $pageId,
                "group" => "widget.performer_application",
                "order" => 1,
            ],
            [
                "key" => "form_id",
                "display_name" => null,
                "value" => $formId,
                "group" => "widget.performer_application",
                "order" => 2,
            ],
        ];

        foreach ($widgetPerformerApplications as $widgetPerformerApplication) {
            Setting::factory()->create(array_merge($widgetPerformerApplication, [
                "created_at" => now(),
                "updated_at" => now(),
            ]));
        }
    }

    private function isModuleFormBuilderActive(): bool
    {
        return app(ModuleService::class)->isModuleActive('FormBuilder');
    }

    private function getFieldGroups(): array
    {
        return [
            [
                'title' => null,
                'order' => 1,
                'fields' => [
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "First name",
                        "name" => "first_name",
                        "placeholder" => "Your first name",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "128",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GN7HRC3",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "Last name",
                        "name" => "last_name",
                        "placeholder" => "Your last name",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "128",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GOS16I9",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-full",
                        "label" => "Company (optional)",
                        "name" => "company_optional",
                        "placeholder" => "Your company name",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "max" => "128",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GP30JPJ",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Email",
                        "title" => "Email",
                        "column" => "is-half",
                        "label" => "Email",
                        "name" => "email",
                        "placeholder" => "Your email address",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "255",
                                "min" => null,
                                "email" => true,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GPQ04TR",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Phone",
                        "title" => "Phone",
                        "column" => "is-half",
                        "label" => "Phone",
                        "name" => "phone",
                        "placeholder" => "Your phone number",
                        "note" => null,
                        "default_value" => ["country" => null, "number" => null],
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => ["rules" => ["required" => true], "message" => []],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GQWQE96",
                        "properties" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "Stage name",
                        "name" => "stage_name",
                        "placeholder" => "Your stage name",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "64",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GRLQHIK",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Select",
                        "title" => "Select",
                        "column" => "is-half",
                        "label" => "Discipline",
                        "name" => "discipline",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "options" => [
                            ["id" => "Acrobat", "value" => "Acrobat"],
                            ["id" => "Acrobatic", "value" => "Acrobatic"],
                            ["id" => "BalanceAct", "value" => "BalanceAct"],
                            ["id" => "Clown", "value" => "Clown"],
                            ["id" => "Dance", "value" => "Dance"],
                            ["id" => "Escapologist", "value" => "Escapologist"],
                            ["id" => "Juggler", "value" => "Juggler"],
                            ["id" => "Magician", "value" => "Magician"],
                            [
                                "id" => "Multidisciplinary Circus",
                                "value" => "Multidisciplinary Circus",
                            ],
                            ["id" => "Music-Acrobat", "value" => "Music-Acrobat"],
                            ["id" => "Music-Clown", "value" => "Music-Clown"],
                            ["id" => "Musician", "value" => "Musician"],
                            ["id" => "Performance", "value" => "Performance"],
                            ["id" => "Pupeteer", "value" => "Pupeteer"],
                            ["id" => "Stiltwalkers", "value" => "Stiltwalkers"],
                            ["id" => "Visual Comedy", "value" => "Visual Comedy"],
                            ["id" => "Other", "value" => "Other"],
                        ],
                        "validation" => ["rules" => ["required" => true], "message" => []],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GS9MNP0",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                ]
            ],
            [
                'title' => "Address",
                'order' => 2,
                'fields' => [
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-full",
                        "label" => "Street address",
                        "name" => "street_address",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "128",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GWS1A9Y",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "City",
                        "name" => "city",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "64",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GXW7S4R",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Postcode",
                        "title" => "Postcode",
                        "column" => "is-half",
                        "label" => "ZIP / Postal code",
                        "name" => "zip__postal_code",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => ["required" => true, "max" => "32", "min" => null],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLDI8G9KHWJ",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Country",
                        "title" => "Country",
                        "column" => "is-half",
                        "label" => "Country",
                        "name" => "country",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "options" => [],
                        "validation" => ["rules" => ["required" => true], "message" => []],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GZEBWLD",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                ]
            ],
            [
                'title' => null,
                'order' => 3,
                'fields' => [
                    [
                        "type" => "Textarea",
                        "title" => "Textarea",
                        "column" => "is-full",
                        "label" => "Tell us about you",
                        "name" => "tell_us_about_you",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => ["required" => true, "max" => "2048", "min" => null],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8GZYYQIM",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Textarea",
                        "title" => "Textarea",
                        "column" => "is-full",
                        "label" => "Describe your performance",
                        "name" => "describe_your_performance",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => ["required" => true, "max" => "2048", "min" => null],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H0MLX6B",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                ]
            ],
            [
                'title' => "Fees per day",
                'order' => 4,
                'fields' => [
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-one-third",
                        "label" => "Corporate gigs",
                        "name" => "corporate_gigs",
                        "placeholder" => "ex: $250",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "16",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H171PJH",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-one-third",
                        "label" => "Private gigs",
                        "name" => "private_gigs",
                        "placeholder" => "ex: $250",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "16",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H247KCH",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-one-third",
                        "label" => "Festival gigs",
                        "name" => "festival_gigs",
                        "placeholder" => "ex: $250",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => "16",
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H28J4LD",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                ]
            ],
            [
                'title' => null,
                'order' => 5,
                'fields' => [
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-one-third",
                        "label" => "Facebook",
                        "name" => "facebook",
                        "placeholder" => "Facebook link",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "max" => null,
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H2HB8LA",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-one-third",
                        "label" => "Twitter",
                        "name" => "twitter",
                        "placeholder" => "Twitter link",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "max" => null,
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H2X5G20",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-one-third",
                        "label" => "Instagram",
                        "name" => "instagram",
                        "placeholder" => "Instagram link",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "max" => null,
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H2YBSPX",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "Youtube",
                        "name" => "youtube",
                        "placeholder" => "Youtube link",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "max" => null,
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H30B2T2",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "Other(s)",
                        "name" => "others",
                        "placeholder" => "Other links (separate by comma)",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "max" => null,
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H31R283",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-full",
                        "label" => "Promotional video",
                        "name" => "promotional_video",
                        "placeholder" => "Youtube or Vimeo link",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => null,
                                "min" => null,
                                "regex" => null,
                            ],
                            "message" => [],
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H4XZQO7",
                        "properties" => [],
                        "data" => [],
                        "attributes" => [],
                    ],
                    [
                        "type" => "FileDragDrop",
                        "title" => "File Upload",
                        "column" => "is-half",
                        "label" => "Performace photo",
                        "name" => "performace_photo",
                        "placeholder" => "Drop files here...",
                        "note" =>
                            "Upload photos of your Performance. Kindly upload up to 10 pictures of your performances, and make sure to pick your best photos, as this might have an impact on your application during the review process.",
                        "default_value" => [],
                        "max_file_number" => "10",
                        "min_file_number" => 0,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "mimes" => ["image"],
                                "max" => "15360",
                            ],
                            "message" => [],
                        ],
                        "is_multiple_upload" => true,
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDLD8H5U52FQ",
                        "properties" => [],
                        "attributes" => [],
                    ],
                ]
            ],
        ];
    }

    public function getDataPageTranslation(): array
    {
        return [
            "structures" => [
                [
                    "componentName" => "Columns",
                    "type" => "columns",
                    "columns" => [
                        [
                            "id" => "IDLD8IP8JW0I",
                            "components" => [
                                ["id" => "IDLD8IPKFX3L", "componentName" => "Heading"],
                            ],
                        ],
                    ],
                    "id" => "IDLD8IPAQ834",
                ],
                [
                    "componentName" => "Columns",
                    "type" => "columns",
                    "columns" => [
                        [
                            "id" => "IDLD8IPJ7PQ6",
                            "components" => [
                                [
                                    "id" => "IDLD8IPXVK3O",
                                    "componentName" => "FormBuilder",
                                ],
                            ],
                        ],
                    ],
                    "id" => "IDLD8IPJ7PH4",
                ],
            ],
            "entities" => [
                "IDLD8IP9OPO8" => [
                    "componentName" => "Columns",
                    "type" => "columns",
                    "config" => [
                        "wrapper" => [
                            "customId" => null,
                            "isFullwidth" => false,
                            "backgroundColor" => null,
                            "backgroundImage" => null,
                            "rounded" => null,
                        ],
                        "section" => ["isIncluded" => false, "size" => null],
                        "columns" => [
                            "isCentered" => false,
                            "column" => [["size" => "auto"]],
                        ],
                        "dimension" => [
                            "style.padding" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                            "style.margin" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                        ],
                    ],
                    "id" => "IDLD8IP9OPO8",
                ],
                "IDLD8IPAQ834" => [
                    "componentName" => "Columns",
                    "type" => "columns",
                    "config" => [
                        "wrapper" => [
                            "customId" => null,
                            "isFullwidth" => false,
                            "backgroundColor" => null,
                            "backgroundImage" => null,
                            "rounded" => null,
                        ],
                        "section" => ["isIncluded" => false, "size" => null],
                        "columns" => [
                            "isCentered" => false,
                            "column" => [["size" => "auto"]],
                        ],
                        "dimension" => [
                            "style.padding" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                            "style.margin" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                        ],
                    ],
                    "id" => "IDLD8IPAQ834",
                ],
                "IDLD8IPJ7PH4" => [
                    "componentName" => "Columns",
                    "type" => "columns",
                    "config" => [
                        "wrapper" => [
                            "customId" => null,
                            "isFullwidth" => false,
                            "backgroundColor" => null,
                            "backgroundImage" => null,
                            "rounded" => null,
                        ],
                        "section" => ["isIncluded" => false, "size" => null],
                        "columns" => [
                            "isCentered" => false,
                            "column" => [["size" => "auto"]],
                        ],
                        "dimension" => [
                            "style.padding" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                            "style.margin" => [
                                "top" => "50",
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                        ],
                    ],
                    "id" => "IDLD8IPJ7PH4",
                ],
                "IDLD8IPKFX3L" => [
                    "title" => "Heading",
                    "componentName" => "Heading",
                    "content" => ["heading" => ["html" => "Performer Application"]],
                    "config" => [
                        "heading" => [
                            "tag" => "h1",
                            "type" => "title",
                            "alignment" => "has-text-centered",
                            "color" => null,
                        ],
                        "dimension" => [
                            "style.padding" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                            "style.margin" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                        ],
                    ],
                    "id" => "IDLD8IPKFX3L",
                ],
                "IDLD8IPXVK3O" => [
                    "title" => "Form Builder",
                    "componentName" => "FormBuilder",
                    "content" => [],
                    "config" => [
                        "form" => ["id" => "performer_application"],
                        "dimension" => [
                            "style.padding" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                            "style.margin" => [
                                "top" => null,
                                "right" => null,
                                "bottom" => null,
                                "left" => null,
                                "unit" => "px",
                            ],
                        ],
                    ],
                    "id" => "IDLD8IPXVK3O",
                ],
            ],
            "media" => [],
        ];
    }
}
