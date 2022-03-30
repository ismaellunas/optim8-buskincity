<?php

namespace Database\Seeders;

class TranslationStringSeeder extends TranslationGroupSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // --- Homepage ---
        $translations = [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'I love programming.',
                'value' => 'I love programming.'
            ],
        ];

        // --- Form builder ---
        // Biodata
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Gender',
                'value' => 'Gender'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Phone',
                'value' => 'Phone'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Education',
                'value' => 'Education'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Next Of Kin',
                'value' => 'Next Of Kin'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Blood Type',
                'value' => 'Blood Type'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'About Me',
                'value' => 'About Me'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Facebook',
                'value' => 'Facebook'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Instagram',
                'value' => 'Instagram'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Skills',
                'value' => 'Skills'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Criminal Record',
                'value' => 'Criminal Record'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Performance Video',
                'value' => 'Performance Video'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Term and Condition',
                'value' => 'Term and Condition'
            ],
        ]);

        // Address
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Address',
                'value' => 'Address'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Post Code',
                'value' => 'Post Code'
            ],
        ]);

        // Documents
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Licence',
                'value' => 'Licence'
            ],
        ]);

        // --- Team Invitation ---
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'You have been invited to join the :team team!',
                'value' => 'You have been invited to join the :team team!'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'If you do not have an account, you may create one by clicking the button below. After creating an account, you may click the invitation acceptance button in this email to accept the team invitation:',
                'value' => 'If you do not have an account, you may create one by clicking the button below. After creating an account, you may click the invitation acceptance button in this email to accept the team invitation:'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Create Account',
                'value' => 'Create Account'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'If you already have an account, you may accept this invitation by clicking the button below:',
                'value' => 'If you already have an account, you may accept this invitation by clicking the button below:'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'You may accept this invitation by clicking the button below:',
                'value' => 'You may accept this invitation by clicking the button below:'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Accept Invitation',
                'value' => 'If you did not expect to receive an invitation to this team, you may discard this email.'
            ],
        ]);

        // --- Notification / message ---
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Whoops! Something went wrong.',
                'value' => 'Whoops! Something went wrong.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'The provided two factor authentication code was invalid.',
                'value' => 'The provided two factor authentication code was invalid.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Your Account is suspended, please contact the support.',
                'value' => 'Your Account is suspended, please contact the support.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'This :Provider sign in account is already associated with another user. Please try a different account.',
                'value' => 'This :Provider sign in account is already associated with another user. Please try a different account.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'You have successfully connected :Provider to your account.',
                'value' => 'You have successfully connected :Provider to your account.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'This :Provider sign in account is already associated with your user.',
                'value' => 'This :Provider sign in account is already associated with your user.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Your Account is suspended, please contact the support.',
                'value' => 'Your Account is suspended, please contact the support.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Accepted file extension: :extensions',
                'value' => 'Accepted file extension: :extensions'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Max file size: :size',
                'value' => 'Max file size: :size'
            ],
        ]);

        // --- Decision ---
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Confirm Password',
                'value' => 'Confirm Password'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'For your security, please confirm your password to continue.',
                'value' => 'For your security, please confirm your password to continue.'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Confirm',
                'value' => 'Confirm'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Cancel',
                'value' => 'Cancel'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Password',
                'value' => 'Password'
            ],
        ]);

        // --- Email ---
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Thanks For Your Donation',
                'value' => 'Thanks For Your Donation'
            ],
        ]);

        // --- Other ---
        $translations = array_merge($translations, [
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Name',
                'value' => 'Name'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Email',
                'value' => 'Email'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Language',
                'value' => 'Language'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Country',
                'value' => 'Country'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'File',
                'value' => 'File'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Type',
                'value' => 'Type'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Page',
                'value' => 'Page'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Post',
                'value' => 'Post'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Category',
                'value' => 'Category'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Url',
                'value' => 'Url'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'User',
                'value' => 'User'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Role Name',
                'value' => 'Role Name'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Translation Manager',
                'value' => 'Translation Manager'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Draft',
                'value' => 'Draft'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Published',
                'value' => 'Published'
            ],
            [
                'locale' => $this->referenceLocale,
                'group' => null,
                'key' => 'Search',
                'value' => 'Search',
            ],
        ]);

        $this->batchCreate($translations);
    }
}
