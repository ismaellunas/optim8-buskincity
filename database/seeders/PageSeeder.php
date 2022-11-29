<?php

namespace Database\Seeders;

use App\Models\{
    Page,
    PageTranslation,
    Setting,
    User,
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages = [
            'homepage' => [
                'title' => 'Homepage',
                'data' => [
                    "structures" => [
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9F23V8OOA",
                                    "components" => [
                                        ["id" => "IDL9F24B2SAM", "componentName" => "Heading"],
                                        ["id" => "IDL9F24PV5HK", "componentName" => "Text"],
                                        ["id" => "IDL9HWUW6SXA", "componentName" => "Button"],
                                    ],
                                ],
                                ["id" => "IDL9F246F7CX", "components" => []],
                            ],
                            "id" => "IDL9F23Z6L9M",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                ["id" => "IDL9F2P3CNR9", "components" => []],
                                [
                                    "id" => "IDL9F2SBNYXM",
                                    "components" => [
                                        ["id" => "IDL9F2SH9C7D", "componentName" => "Heading"],
                                        ["id" => "IDL9F2SZZSVP", "componentName" => "Text"],
                                    ],
                                ],
                                ["id" => "IDL9F2SCMF48", "components" => []],
                            ],
                            "id" => "IDL9F2S7CG86",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9F3H4532S",
                                    "components" => [
                                        ["id" => "IDL9FB86ONQ2", "componentName" => "Video"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9F3M9BWOJ",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9GHKP2NWS",
                                    "components" => [
                                        ["id" => "IDL9GHKP2N8C", "componentName" => "Heading"],
                                    ],
                                ],
                                [
                                    "id" => "IDL9GHKYQ7ZI",
                                    "components" => [
                                        ["id" => "IDL9GU0FUF81", "componentName" => "Button"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHKP2NXW",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9GHL1MGJF",
                                    "components" => [
                                        [
                                            "id" => "IDL9GHLEVHKM",
                                            "componentName" => "LatestPost",
                                        ],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHL1MGNF",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9GHO4U7WI",
                                    "components" => [
                                        ["id" => "IDL9GHO4U76N", "componentName" => "Heading"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHO4U7O6",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                ["id" => "IDL9GHOEZREZ", "components" => []],
                                [
                                    "id" => "IDL9GHOQXSBK",
                                    "components" => [
                                        ["id" => "IDL9GHOZB81R", "componentName" => "Heading"],
                                        ["id" => "IDL9GHP8X7V1", "componentName" => "Text"],
                                        ["id" => "IDL9GHPP6VYD", "componentName" => "Button"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHOEZRDV",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9GHZWOEP1",
                                    "components" => [
                                        ["id" => "IDL9GHZWOEZF", "componentName" => "Heading"],
                                        ["id" => "IDL9GHZWOF7S", "componentName" => "Text"],
                                        ["id" => "IDL9GHZWOFPM", "componentName" => "Button"],
                                    ],
                                ],
                                ["id" => "IDL9GHZWOEJL", "components" => []],
                            ],
                            "id" => "IDL9GHZWOERZ",
                        ],
                    ],
                    "entities" => [
                        "IDL9F23Z6L9M" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "customId" => null,
                                    "isFullwidth" => false,
                                    "backgroundColor" => "has-background-light",
                                    "backgroundImage" => null,
                                    "rounded" => "is-rounded-small",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => "100",
                                        "right" => "50",
                                        "bottom" => "100",
                                        "left" => "50",
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
                                "section" => ["isIncluded" => false, "size" => "is-medium"],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9F23Z6L9M",
                        ],
                        "IDL9F24B2SAM" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Homepage"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h1",
                                    "type" => "title",
                                    "alignment" => null,
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
                            "id" => "IDL9F24B2SAM",
                        ],
                        "IDL9F24PV5HK" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p><b>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</b></p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
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
                                        "top" => "20",
                                        "right" => null,
                                        "bottom" => "20",
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDL9F24PV5HK",
                        ],
                        "IDL9F2S7CG86" => [
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
                                "section" => ["isIncluded" => true, "size" => "is-medium"],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                        ["size" => "auto"],
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9F2S7CG86",
                        ],
                        "IDL9F2SH9C7D" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Lorem Ipsum"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h2",
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
                            "id" => "IDL9F2SH9C7D",
                        ],
                        "IDL9F2SZZSVP" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
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
                                        "top" => "30",
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDL9F2SZZSVP",
                        ],
                        "IDL9F3M9BWOJ" => [
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
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => null],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9F3M9BWOJ",
                        ],
                        "IDL9FB86ONQ2" => [
                            "title" => "Video",
                            "componentName" => "Video",
                            "content" => [],
                            "config" => [
                                "video" => [
                                    "url" => "https://www.youtube.com/watch?v=BHACKCNDMW8",
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
                                        "right" => "20",
                                        "bottom" => null,
                                        "left" => "20",
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDL9FB86ONQ2",
                        ],
                        "IDL9GHKP2N8C" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Latest Articles"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h2",
                                    "type" => "title",
                                    "alignment" => null,
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
                            "id" => "IDL9GHKP2N8C",
                        ],
                        "IDL9GHKP2NXW" => [
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
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "100",
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => null],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHKP2NXW",
                        ],
                        "IDL9GHL1MGNF" => [
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
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "20",
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => null],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHL1MGNF",
                        ],
                        "IDL9GHLEVHKM" => [
                            "title" => "Latest Post",
                            "componentName" => "LatestPost",
                            "content" => [],
                            "config" => [
                                "post" => ["categoryId" => null, "limit" => 3],
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
                            "id" => "IDL9GHLEVHKM",
                        ],
                        "IDL9GHO4U76N" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Lorem Ipsum"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h2",
                                    "type" => "title",
                                    "alignment" => null,
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
                            "id" => "IDL9GHO4U76N",
                        ],
                        "IDL9GHO4U7O6" => [
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
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "100",
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => null],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHO4U7O6",
                        ],
                        "IDL9GHOEZRDV" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "customId" => null,
                                    "isFullwidth" => false,
                                    "backgroundColor" => "has-background-light",
                                    "backgroundImage" => null,
                                    "rounded" => "is-rounded-small",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => "30",
                                        "right" => "30",
                                        "bottom" => "30",
                                        "left" => "30",
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "50",
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => null],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHOEZRDV",
                        ],
                        "IDL9GHOZB81R" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Lorem Ipsum"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h3",
                                    "type" => "title",
                                    "alignment" => null,
                                    "color" => null,
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => "75",
                                        "bottom" => null,
                                        "left" => "25",
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
                            "id" => "IDL9GHOZB81R",
                        ],
                        "IDL9GHP8X7V1" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
                                    "color" => null,
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => "75",
                                        "bottom" => null,
                                        "left" => "25",
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "30",
                                        "right" => null,
                                        "bottom" => "30",
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHP8X7V1",
                        ],
                        "IDL9GHPP6VYD" => [
                            "title" => "Button",
                            "componentName" => "Button",
                            "content" => ["button" => ["text" => "Apply Now", "icon" => null]],
                            "config" => [
                                "button" => [
                                    "link" => "#",
                                    "target" => null,
                                    "color" => "is-primary",
                                    "isLight" => false,
                                    "size" => null,
                                    "width" => null,
                                    "style" => "is-rounded",
                                    "position" => null,
                                    "iconPosition" => "left",
                                    "textWeight" => null,
                                ],
                                "visibility" => [
                                    "device" => null,
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => "25",
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
                            "id" => "IDL9GHPP6VYD",
                        ],
                        "IDL9GHZWOEZF" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Lorem Ipsum"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h3",
                                    "type" => "title",
                                    "alignment" => null,
                                    "color" => null,
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => "25",
                                        "bottom" => null,
                                        "left" => "75",
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
                            "id" => "IDL9GHZWOEZF",
                        ],
                        "IDL9GHZWOF7S" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
                                    "color" => null,
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => "25",
                                        "bottom" => null,
                                        "left" => "75",
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "30",
                                        "right" => null,
                                        "bottom" => "30",
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHZWOF7S",
                        ],
                        "IDL9GHZWOFPM" => [
                            "title" => "Button",
                            "componentName" => "Button",
                            "content" => ["button" => ["text" => "Sign Up", "icon" => null]],
                            "config" => [
                                "button" => [
                                    "link" => "http://localhost:8000/login",
                                    "target" => null,
                                    "color" => "is-primary",
                                    "isLight" => false,
                                    "size" => null,
                                    "width" => null,
                                    "style" => "is-rounded",
                                    "position" => null,
                                    "iconPosition" => "left",
                                    "textWeight" => null,
                                ],
                                "visibility" => [
                                    "device" => null,
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => "75",
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
                            "id" => "IDL9GHZWOFPM",
                        ],
                        "IDL9GHZWOERZ" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "customId" => null,
                                    "isFullwidth" => false,
                                    "backgroundColor" => "has-background-light",
                                    "backgroundImage" => null,
                                    "rounded" => "is-rounded-small",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => "30",
                                        "right" => "30",
                                        "bottom" => "30",
                                        "left" => "30",
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "50",
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => null],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [
                                        ["size" => "auto"],
                                        ["size" => "auto"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHZWOERZ",
                        ],
                        "IDL9GU0FUF81" => [
                            "title" => "Button",
                            "componentName" => "Button",
                            "content" => [
                                "button" => ["text" => "View All Articles", "icon" => null],
                            ],
                            "config" => [
                                "button" => [
                                    "link" => "http://localhost:8000/blog",
                                    "target" => null,
                                    "color" => "is-primary",
                                    "isLight" => false,
                                    "size" => null,
                                    "width" => null,
                                    "style" => "is-rounded is-outlined",
                                    "position" => "is-pulled-right",
                                    "iconPosition" => "left",
                                    "textWeight" => null,
                                ],
                                "visibility" => [
                                    "device" => null,
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
                            "id" => "IDL9GU0FUF81",
                        ],
                        "IDL9HWUW6SXA" => [
                            "title" => "Button",
                            "componentName" => "Button",
                            "content" => ["button" => ["text" => "Learn More", "icon" => null]],
                            "config" => [
                                "button" => [
                                    "link" => null,
                                    "target" => null,
                                    "color" => "is-primary",
                                    "isLight" => false,
                                    "size" => null,
                                    "width" => null,
                                    "style" => "is-rounded",
                                    "position" => null,
                                    "iconPosition" => "left",
                                    "textWeight" => null,
                                ],
                                "visibility" => [
                                    "device" => null,
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
                            "id" => "IDL9HWUW6SXA",
                        ],
                    ],
                    "media" => [],
                ],
            ],
            'about' => [
                'title' => 'about',
                'data' => [
                    "structures" => [
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9F23V8OOA",
                                    "components" => [
                                        ["id" => "IDL9F24B2SAM", "componentName" => "Heading"],
                                        ["id" => "IDL9F24PV5HK", "componentName" => "Text"],
                                        ["id" => "IDL9HWUW6SXA", "componentName" => "Button"],
                                    ],
                                ],
                                ["id" => "IDL9F246F7CX", "components" => []],
                            ],
                            "id" => "IDL9F23Z6L9M",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9F2P3CNR9",
                                    "components" => [
                                        ["id" => "IDL9F2SH9C7D", "componentName" => "Heading"],
                                    ],
                                ],
                                [
                                    "id" => "IDL9F2SBNYXM",
                                    "components" => [
                                        ["id" => "IDL9F2SZZSVP", "componentName" => "Text"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9F2S7CG86",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9GHOEZREZ",
                                    "components" => [
                                        ["id" => "IDL9GHOZB81R", "componentName" => "Heading"],
                                        ["id" => "IDL9GHP8X7V1", "componentName" => "Text"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHOEZRDV",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL9GHKP2NWS",
                                    "components" => [
                                        ["id" => "IDL9GHKP2N8C", "componentName" => "Heading"],
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHKP2NXW",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDLAJ1ANVVKI",
                                    "components" => [
                                        ["id" => "IDLAJ1ANVVP0", "componentName" => "Text"],
                                    ],
                                ],
                                [
                                    "id" => "IDLAJ1ANVVM0",
                                    "components" => [
                                        ["id" => "IDLAJ1D1LPZ4", "componentName" => "Text"],
                                    ],
                                ],
                            ],
                            "id" => "IDLAJ1ANVV1G",
                        ],
                    ],
                    "entities" => [
                        "IDL9F23Z6L9M" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "customId" => null,
                                    "isFullwidth" => false,
                                    "backgroundColor" => "has-background-light",
                                    "backgroundImage" => null,
                                    "rounded" => "is-rounded-small",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => "100",
                                        "right" => "50",
                                        "bottom" => "100",
                                        "left" => "50",
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
                                "section" => ["isIncluded" => false, "size" => "is-medium"],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [["size" => "auto"], ["size" => "auto"]],
                                ],
                            ],
                            "id" => "IDL9F23Z6L9M",
                        ],
                        "IDL9F24B2SAM" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "About"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h1",
                                    "type" => "title",
                                    "alignment" => null,
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
                            "id" => "IDL9F24B2SAM",
                        ],
                        "IDL9F24PV5HK" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p><b>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</b></p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
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
                                        "top" => "20",
                                        "right" => null,
                                        "bottom" => "20",
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDL9F24PV5HK",
                        ],
                        "IDL9F2S7CG86" => [
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
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => true, "size" => "is-medium"],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [["size" => "auto"], ["size" => "auto"]],
                                ],
                            ],
                            "id" => "IDL9F2S7CG86",
                        ],
                        "IDL9F2SH9C7D" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Lorem Ipsum"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h2",
                                    "type" => "title",
                                    "alignment" => null,
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
                            "id" => "IDL9F2SH9C7D",
                        ],
                        "IDL9F2SZZSVP" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
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
                            "id" => "IDL9F2SZZSVP",
                        ],
                        "IDL9GHKP2N8C" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Lorem Ipsum"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h2",
                                    "type" => "title",
                                    "alignment" => null,
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
                            "id" => "IDL9GHKP2N8C",
                        ],
                        "IDL9GHKP2NXW" => [
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
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "100",
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => null],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [["size" => "auto"]],
                                ],
                            ],
                            "id" => "IDL9GHKP2NXW",
                        ],
                        "IDL9GHOEZRDV" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "customId" => null,
                                    "isFullwidth" => false,
                                    "backgroundColor" => "has-background-light",
                                    "backgroundImage" => null,
                                    "rounded" => "is-rounded-small",
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
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => true, "size" => "is-medium"],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [["size" => "auto"]],
                                ],
                            ],
                            "id" => "IDL9GHOEZRDV",
                        ],
                        "IDL9GHOZB81R" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Lorem Ipsum"]],
                            "config" => [
                                "heading" => [
                                    "tag" => "h2",
                                    "type" => "title",
                                    "alignment" => null,
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
                            "id" => "IDL9GHOZB81R",
                        ],
                        "IDL9GHP8X7V1" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
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
                                        "top" => "30",
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDL9GHP8X7V1",
                        ],
                        "IDL9HWUW6SXA" => [
                            "title" => "Button",
                            "componentName" => "Button",
                            "content" => ["button" => ["text" => "Learn More", "icon" => null]],
                            "config" => [
                                "button" => [
                                    "link" => null,
                                    "target" => null,
                                    "color" => "is-primary",
                                    "isLight" => false,
                                    "size" => null,
                                    "width" => null,
                                    "style" => "is-rounded",
                                    "position" => null,
                                    "iconPosition" => "left",
                                ],
                                "visibility" => ["device" => null],
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
                            "id" => "IDL9HWUW6SXA",
                        ],
                        "IDLAJ1ANVVP0" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscinLorem IpsumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.g elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
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
                            "id" => "IDLAJ1ANVVP0",
                        ],
                        "IDLAJ1ANVV1G" => [
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
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                    "style.margin" => [
                                        "top" => "25",
                                        "right" => "50",
                                        "bottom" => null,
                                        "left" => "50",
                                        "unit" => "px",
                                    ],
                                ],
                                "section" => ["isIncluded" => false, "size" => "is-medium"],
                                "columns" => [
                                    "isCentered" => false,
                                    "column" => [["size" => "auto"], ["size" => "auto"]],
                                ],
                            ],
                            "id" => "IDLAJ1ANVV1G",
                        ],
                        "IDLAJ1D1LPZ4" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscinLorem IpsumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.g elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null,
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
                            "id" => "IDLAJ1D1LPZ4",
                        ],
                    ],
                    "media" => [],
                ],
            ],
            'street_performers' => [
                'title' => 'Street Performers',
                'data' => [
                    "structures" => [
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDLAJ1P0B74T",
                                    "components" => [
                                        ["id" => "IDLAJ1P571Q1", "componentName" => "Heading"],
                                        ["id" => "IDLAJ1PQ2YH4", "componentName" => "Text"],
                                    ],
                                ],
                            ],
                            "id" => "IDLAJ1P18VRY",
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDLAJ1P0B74T",
                                    "components" => [
                                        ["id" => "IDLAJ1QZ4CMG", "componentName" => "UserList"],
                                    ],
                                ],
                            ],
                            "id" => "IDLAJ1QJV2DQ",
                        ],
                    ],
                    "entities" => [
                        "IDLAJ1P18VRY" => [
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
                                    "isCentered" => true,
                                    "column" => [["size" => "8"]],
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
                                        "bottom" => "50",
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDLAJ1P18VRY",
                        ],
                        "IDLAJ1P571Q1" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => ["heading" => ["html" => "Street Performers"]],
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
                            "id" => "IDLAJ1P571Q1",
                        ],
                        "IDLAJ1PQ2YH4" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" =>
                                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>",
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
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
                                        "top" => "30",
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDLAJ1PQ2YH4",
                        ],
                        "IDLAJ1QJV2DQ" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "customId" => null,
                                    "isFullwidth" => false,
                                    "backgroundColor" => "has-background-light",
                                    "backgroundImage" => null,
                                    "rounded" => null,
                                ],
                                "section" => ["isIncluded" => true, "size" => "is-small"],
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
                                        "bottom" => "50",
                                        "left" => null,
                                        "unit" => "px",
                                    ],
                                ],
                            ],
                            "id" => "IDLAJ1QJV2DQ",
                        ],
                        "IDLAJ1QZ4CMG" => [
                            "title" => "User List",
                            "componentName" => "UserList",
                            "content" => ["heading" => ["html" => "User List"], "roles" => []],
                            "config" => [
                                "list" => [
                                    "roles" => [4],
                                    "excludedId" => null,
                                    "orderBy" => null,
                                    "countries" => [],
                                    "types" => [],
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
                            "id" => "IDLAJ1QZ4CMG",
                        ],
                    ],
                    "media" => [],
                ],
            ],
        ];

        $adminUser = User::find(1);

        $homepage = $this->addPage($pages['homepage'], $adminUser);

        $this->setPageToHomepage($homepage->id);

        $this->addPage($pages['about'], $adminUser);
        $this->addPage($pages['street_performers'], $adminUser);
    }

    private function addPage(array $page, User $adminUser): Page
    {
        return Page::factory()
            ->hasTranslations(1, [
                'title' => $page['title'],
                'slug' => Str::slug($page['title']),
                'status' => PageTranslation::STATUS_PUBLISHED,
                'data' => $page['data'],
            ])
            ->for($adminUser, 'author')
            ->create();
    }

    private function setPageToHomepage(int $pageId): void
    {
        $setting = Setting::firstOrNew(['key' => 'home_page']);

        $setting->value = $pageId;

        $setting->save();
    }
}
