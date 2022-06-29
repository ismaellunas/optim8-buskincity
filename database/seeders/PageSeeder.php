<?php

namespace Database\Seeders;

use App\Models\{
    Page,
    PageTranslation,
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
        $adminUser = User::find(1);
        $pages = [
            [
                'title' => 'Dummy Page',
                'data' => [
                    "structures" => [
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL4PBMGR07X",
                                    "components" => [
                                        [
                                            "id" => "IDL0GDCQ2FNJ",
                                            "componentName" => "Heading"
                                        ]
                                    ]
                                ]
                            ],
                            "id" => "IDL4PBMJ6GPF"
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL4PBMGR03Q",
                                    "components" => [
                                        [
                                            "id" => "IDL0GDEM20UE",
                                            "componentName" => "Text"
                                        ],
                                        [
                                            "id" => "IDL0GDML4ZC9",
                                            "componentName" => "Button"
                                        ]
                                    ]
                                ],
                                [
                                    "id" => "IDL4PBMGR0Y8",
                                    "components" => [
                                        [
                                            "id" => "IDL0GDDBD53P",
                                            "componentName" => "CardText"
                                        ]
                                    ]
                                ]
                            ],
                            "id" => "IDL4PBMRSZW5"
                        ],
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL4PBMGR07X",
                                    "components" => [
                                        [
                                            "id" => "IDL0GDFNUXL8",
                                            "componentName" => "Tabs"
                                        ]
                                    ]
                                ]
                            ],
                            "id" => "IDL4PBN90XUP"
                        ]
                    ],
                    "entities" => [
                        "IDL0GDCQ2FNJ" => [
                            "title" => "Heading",
                            "componentName" => "Heading",
                            "content" => [
                                "heading" => [
                                    "html" => "Dummy Page"
                                ]
                            ],
                            "config" => [
                                "heading" => [
                                    "tag" => "h1",
                                    "type" => "title",
                                    "alignment" => null
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ]
                            ],
                            "id" => "IDL0GDCQ2FNJ"
                        ],
                        "IDL0GDDBD53P" => [
                            "title" => "Card Text",
                            "componentName" => "CardText",
                            "content" => [
                                "cardContent" => [
                                    "content" => [
                                        "html" => "<p><strong>Lorem ipsum</strong> dolor sit amet consectetur, adipisicing elit. Ad, quidem iure qui, dignissimos necessitatibus commodi laborum nisi atque quo quos libero pariatur deleniti natus laboriosam fuga, nemo non sequi tempore!</p>"
                                    ],
                                    "media" => [
                                    ]
                                ]
                            ],
                            "config" => [
                                "content" => [
                                    "size" => null
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ]
                            ],
                            "id" => "IDL0GDDBD53P"
                        ],
                        "IDL0GDEM20UE" => [
                            "title" => "Text",
                            "componentName" => "Text",
                            "content" => [
                                "html" => "<h3><strong>What is Lorem Ipsum?</strong></h3><p><strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit. Aenean aliquet risus eu risus finibus, ac accumsan ex aliquam. Duis et libero elementum, commodo mauris maximus, fringilla massa. Integer pellentesque neque at mauris sodales fringilla. Nunc sit amet nunc arcu. Pellentesque viverra quam a congue consequat. Nullam eget dui tempus mauris congue vulputate at et elit. Pellentesque efficitur eleifend ornare. Aliquam sollicitudin, mauris ut commodo volutpat, lacus risus condimentum turpis, at egestas orci diam et mauris. Donec malesuada facilisis ex et vulputate. Aenean in est eget odio rhoncus egestas non id ante. Nam ligula sem, aliquam nec aliquet eget, aliquam in odio. Pellentesque habitant morbi tristique senectus et netuRead Mores et malesuada fames ac turpis egestas.</p>"
                            ],
                            "config" => [
                                "text" => [
                                    "size" => null,
                                    "alignment" => null
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ]
                            ],
                            "id" => "IDL0GDEM20UE"
                        ],
                        "IDL0GDFNUXL8" => [
                            "title" => "Tabs",
                            "componentName" => "Tabs",
                            "content" => [
                                "tabs" => [
                                    [
                                        "name" => "Dummy Text",
                                        "icon" => "fas fa-text",
                                        "html" => "<p><strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit. Aenean aliquet risus eu risus finibus, ac accumsan ex aliquam. Duis et libero elementum, commodo mauris maximus, fringilla massa. Integer pellentesque neque at mauris sodales fringilla. Nunc sit amet nunc arcu. Pellentesque viverra quam a congue consequat. Nullam eget dui tempus mauris congue vulputate at et elit. Pellentesque efficitur eleifend ornare. Aliquam sollicitudin, mauris ut commodo volutpat, lacus risus condimentum turpis, at egestas orci diam et mauris. Donec malesuada facilisis ex et vulputate. Aenean in est eget odio rhoncus egestas non id ante. Nam ligula sem, aliquam nec aliquet eget, aliquam in odio. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>"
                                    ],
                                    [
                                        "name" => "Table",
                                        "icon" => "fas fa-table",
                                        "html" => '<table style="border-collapse: collapse; width: 100%;" border="1"><tbody><tr><td style="width: 99.8762%;"><strong>Category</strong></td></tr><tr><td style="width: 99.8762%;">News</td></tr><tr><td style="width: 99.8762%;">Sport</td></tr></tbody></table>'
                                    ],
                                    [
                                        "name" => "Category Lists",
                                        "icon" => "fas fa-list",
                                        "html" => '<h3>Category Lists:</h3><ul><li>News</li><li>Sport</li><li>Technology</li></ul>'
                                    ]
                                ],
                                "template" => [
                                    "name" => null,
                                    "icon" => null,
                                    "html" => null
                                ]
                            ],
                            "config" => [
                                "tabs" => [
                                    "alignment" => null,
                                    "size" => null,
                                    "style" => "is-boxed",
                                    "width" => null
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ]
                            ],
                            "id" => "IDL0GDFNUXL8"
                        ],
                        "IDL0GDML4ZC9" => [
                            "title" => "Button",
                            "componentName" => "Button",
                            "content" => [
                                "button" => [
                                    "text" => "Read More",
                                    "icon" => null
                                ]
                            ],
                            "config" => [
                                "button" => [
                                    "link" => "https://www.lipsum.com/",
                                    "target" => "_blank",
                                    "color" => "is-link",
                                    "isLight" => true,
                                    "size" => null,
                                    "width" => "is-fullwidth",
                                    "style" => "is-rounded",
                                    "iconPosition" => null
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ]
                            ],
                            "id" => "IDL0GDML4ZC9"
                        ],
                        "IDL4PBMJ6GPF" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "isFullwidth" => false,
                                    "backgroundColor" => "",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ],
                                'section' => [
                                    'isIncluded' => false,
                                    'size' => null,
                                ]
                            ],
                            "id" => "IDL4PBMJ6GPF"
                        ],
                        "IDL4PBMRSZW5" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "isFullwidth" => false,
                                    "backgroundColor" => "",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ],
                                'section' => [
                                    'isIncluded' => false,
                                    'size' => null,
                                ]
                            ],
                            "id" => "IDL4PBMRSZW5"
                        ],
                        "IDL4PBN90XUP" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "isFullwidth" => false,
                                    "backgroundColor" => "",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ],
                                'section' => [
                                    'isIncluded' => false,
                                    'size' => null,
                                ]
                            ],
                            "id" => "IDL4PBN90XUP"
                        ]
                    ],
                    "media" => []
                ],
            ],
            [
                'title' => 'FAQ',
                'data' => [
                    "structures" => [
                        [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "columns" => [
                                [
                                    "id" => "IDL4PBL5QMK8",
                                    "components" => [
                                        [
                                            "id" => "IDL0GDUPE90F",
                                            "componentName" => "Faq"
                                        ]
                                    ]
                                ]
                            ],
                            "id" => "IDL4PBLOSY0U"
                        ]
                    ],
                    "entities" => [
                        "IDL0GDUPE90F" => [
                            "title" => "FAQ",
                            "componentName" => "Faq",
                            "content" => [
                                "heading" => [
                                    "html" => "FAQ"
                                ],
                                "faqContent" => [
                                    "contents" => [
                                        [
                                            "id" => "IDL0GDUQZQ0S",
                                            "question" => "What is Lorem Ipsum?",
                                            "answer" => "<p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"
                                        ],
                                        [
                                            "id" => "IDL0GDV1VMEH",
                                            "question" => "Why do we use it?",
                                            "answer" => "<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here, content here, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for lorem ipsum will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>"
                                        ],
                                        [
                                            "id" => "IDL0GDVA02ZW",
                                            "question" => "Where does it come from?",
                                            "answer" => '<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>'
                                        ],
                                        [
                                            "id" => "IDL0GDVK0YSX",
                                            "question" => "Where can I get some?",
                                            "answer" => "<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which dont look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isnt anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>"
                                        ]
                                    ],
                                    "template" => [
                                        "question" => [
                                            "id" => "IDL0GDVK0YSX",
                                            "question" => null,
                                            "answer" => null
                                        ]
                                    ]
                                ]
                            ],
                            "config" => [
                                "heading" => [
                                    "tag" => "h1",
                                    "type" => "title",
                                    "alignment" => null
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ]
                            ],
                            "id" => "IDL0GDUPE90F"
                        ],
                        "IDL4PBLOSY0U" => [
                            "componentName" => "Columns",
                            "type" => "columns",
                            "config" => [
                                "wrapper" => [
                                    "isFullwidth" => false,
                                    "backgroundColor" => "",
                                ],
                                "dimension" => [
                                    "style.padding" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ],
                                    "style.margin" => [
                                        "top" => null,
                                        "right" => null,
                                        "bottom" => null,
                                        "left" => null,
                                        "unit" => "px"
                                    ]
                                ],
                                'section' => [
                                    'isIncluded' => false,
                                    'size' => null,
                                ]
                            ],
                            "id" => "IDL4PBLOSY0U"
                        ]
                    ],
                    "media" => []
                ],
            ]
        ];

        foreach ($pages as $page) {
            Page::factory()
                ->hasTranslations(1, [
                    'title' => $page['title'],
                    'slug' => Str::slug($page['title']),
                    'status' => PageTranslation::STATUS_PUBLISHED,
                    'data' => $page['data'],
                ])
                ->for($adminUser, 'author')
                ->create();
        }
    }
}
