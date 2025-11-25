# BuskinCity Platform - Comprehensive Codebase Documentation

## Executive Summary

**BuskinCity** is a sophisticated multi-tenant platform built with Laravel 9, Vue.js 3, and Inertia.js, designed to connect performers (street artists, musicians, entertainers) with audiences and venue spaces. The platform enables performers to create profiles, manage bookings and events, accept donations, and showcase their talent in designated spaces across cities.

The application leverages a modular architecture with four main feature modules: **Booking**, **Space**, **FormBuilder**, and **Ecommerce** (powered by Lunar PHP). It implements a comprehensive role-based permission system, multi-language support, and a custom page builder for dynamic content creation. The platform integrates with Stripe for payments, Cloudinary for media management, and Google Maps for location-based features.

Built on a progressive web app (PWA) foundation with offline capabilities, BuskinCity provides both admin and frontend interfaces through Inertia.js, ensuring seamless single-page application experience while maintaining SEO benefits through server-side rendering.

---

## Technical Stack Details

### Backend Framework
- **Laravel 9.x** - PHP 8.1+
- **Database**: PostgreSQL (pgsql)
- **Cache/Sessions**: Redis
- **Queue**: Sync (configurable to Redis)
- **Search**: Scout with database driver

### Frontend Stack
- **Vue.js 3.2.2** - Composition API
- **Inertia.js 1.0.2** - Server-side routing with SPA experience
- **Vite 4.1.4** - Build tool and dev server
- **Bulma CSS Framework** - UI styling
- **TailwindCSS** - Utility-first CSS (configured)

### Key Laravel Packages

#### Authentication & Authorization
- `laravel/jetstream: ^2.3` - Authentication scaffolding
- `laravel/sanctum: ^2.6` - API token authentication
- `laravel/socialite: ^5.2` - OAuth authentication
- `joelbutcher/socialstream: ^4.1` - Social login integration
- `spatie/laravel-permission: ^5.5` - Role & permission management

#### E-commerce & Payments
- `lunarphp/core: ^0.1` - E-commerce foundation
- `stripe/stripe-php: ^10` - Payment processing
- `laravel/fortify` - Backend for authentication features

#### Content & Localization
- `astrotomic/laravel-translatable: ^11.10` - Model translations
- `mcamara/laravel-localization: ^1.7` - Route localization
- `spatie/laravel-translation-loader: ^2.7` - DB-based translations

#### Media & Files
- `cloudinary-labs/cloudinary-laravel: ^2.0` - Cloud media storage
- `maatwebsite/excel: ^3.1` - Excel imports/exports

#### Utilities
- `nwidart/laravel-modules: ^9.0` - Modular structure
- `cviebrock/eloquent-sluggable: ^9.0` - Auto-slugs
- `kalnoy/nestedset: ^6.0` - Nested set model (categories)
- `tightenco/ziggy: ^1.0` - Laravel routes in JavaScript
- `qirolab/laravel-themer: ^2.1` - Theme management
- `kodeine/laravel-meta: ^2.1` - Meta data management

#### Development
- `laravel/telescope: ^4.10` - Debugging assistant
- `laravel/dusk: ^7.7` - Browser testing
- `barryvdh/laravel-debugbar: ^3.6` - Debug toolbar

### Frontend Packages

#### Vue Ecosystem
- `@inertiajs/vue3: ~1.0.2` - Inertia Vue adapter
- `vue-loading-overlay: ^6.0.3` - Loading indicators
- `vue-sweetalert2: ^5.0.2` - Alert dialogs
- `vuedraggable: ^4.1.0` - Drag & drop
- `vue-social-sharing: ^4.0.0-alpha4` - Social share buttons

#### Form & Input
- `@vuepic/vue-datepicker: ^5.3.0` - Date picker
- `vue-filepond: ^7.0.3` - File uploads
- `vue-cropperjs: ^5.0.0` - Image cropping
- `@tinymce/tinymce-vue: ^4.0.4` - Rich text editor

#### Maps & Location
- `@googlemaps/js-api-loader: ^1.14.3` - Google Maps
- `@googlemaps/markerclusterer: ^2.0.14` - Map clustering

#### Utilities
- `moment: ^2.29.4` - Date manipulation
- `sweetalert2: ^11.21.0` - Beautiful alerts
- `easyqrcodejs: ^4.4.10` - QR code generation
- `floating-vue: ^5.2.2` - Tooltips & popovers

---

## Architecture Overview

### Project Structure

```
optim8-buskincity/
├── app/                          # Core application
│   ├── Actions/                  # Business logic actions (24 files)
│   ├── Console/                  # Artisan commands (35 files)
│   ├── Entities/                 # Domain entities (82 files)
│   ├── Helpers/                  # Helper functions (10 files)
│   ├── Http/                     # Controllers & middleware (131 files)
│   │   ├── Controllers/          # Main controllers
│   │   └── Middleware/          # Custom middleware
│   ├── Models/                   # Eloquent models (27 files)
│   ├── Policies/                 # Authorization policies (11 files)
│   ├── Services/                 # Service classes (33 files)
│   ├── Traits/                   # Reusable traits (10 files)
│   └── View/                     # View composers (41 files)
├── modules/                      # Feature modules
│   ├── Booking/                  # Event booking system
│   ├── Ecommerce/                # Product & order management
│   ├── FormBuilder/              # Dynamic form builder
│   └── Space/                    # Venue/space management
├── resources/
│   ├── js/                       # Vue.js frontend
│   │   ├── Pages/                # Inertia pages (93 files)
│   │   ├── Components/           # Vue components
│   │   ├── Biz/                  # Business components (139 files)
│   │   ├── Layouts/              # Layout components
│   │   └── Mixins/               # Vue mixins (21 files)
│   ├── sass/                     # Stylesheets
│   └── views/                    # Blade templates (38 files)
├── database/
│   ├── migrations/               # Database migrations (46 core + modules)
│   ├── seeders/                  # Database seeders (29 files)
│   └── factories/                # Model factories (17 files)
├── routes/                       # Route definitions
│   ├── web.php                   # Public routes
│   ├── admin.php                 # Admin routes
│   └── api.php                   # API routes
├── config/                       # Configuration files (43 files)
├── themes/                       # Theme assets
│   └── buskincity/               # Active theme
└── tests/                        # Test suite (85 files)
```

### Architectural Patterns

#### 1. **Modular Monolith**
The application uses `nwidart/laravel-modules` to organize features into self-contained modules:
- **Booking**: Event scheduling and order management
- **Space**: Venue and location management
- **FormBuilder**: Dynamic form creation and entry processing
- **Ecommerce**: Product catalog (uses Lunar PHP)

Each module has its own:
- Routes (`Routes/web.php`, `Routes/api.php`)
- Controllers (`Http/Controllers/`)
- Models/Entities (`Entities/`)
- Migrations (`Database/Migrations/`)
- Resources (`Resources/assets/js/Pages/`)

#### 2. **Service Layer Pattern**
Business logic is encapsulated in service classes:
- `App\Services\MediaService` - Media upload/management
- `App\Services\UserService` - User operations
- `App\Services\TranslationService` - Multi-language handling
- `App\Services\SettingService` - Configuration management
- `Modules\Booking\Services\ScheduleService` - Booking logic

#### 3. **Repository Pattern (Implicit)**
Models act as repositories with custom query scopes:
```php
User::search('john')
    ->inRoles([1, 2])
    ->emailVerified()
    ->available()
    ->get();
```

#### 4. **Policy-Based Authorization**
Uses Laravel policies for fine-grained access control:
- `App\Policies\UserPolicy`
- `App\Policies\PagePolicy`
- `Modules\Booking\Policies\ProductPolicy`

#### 5. **Observer Pattern**
Model observers handle side effects:
- `App\Observers\UserObserver`
- `App\Observers\PageObserver`
- `Modules\Space\Observers\SpaceObserver`

#### 6. **Component-Based Frontend**
Reusable Vue components in `resources/js/Biz/`:
- Form components (Input, Checkbox, Dropdown, etc.)
- UI components (Button, Card, Modal, etc.)
- Business logic components (FileDragDrop, EditorTinymce, etc.)

---

## Feature Catalog

### 🔐 Authentication & User Management

#### Features
- **Multi-provider Authentication**
  - Email/password login
  - OAuth: Google, Facebook, GitHub
  - Two-factor authentication (2FA)
  - Email verification
  
- **User Roles**
  - Super Administrator
  - Administrator
  - Performer (street artists, musicians)
  - Regular Users

- **User Profile**
  - Profile photo (Cloudinary storage)
  - Multi-language preference
  - Country/location settings
  - Connected social accounts
  - Custom metadata via `UserMeta` model
  - Public profile pages with customizable URLs
  - QR code generation for profiles

#### Backend Implementation
- **Controllers**
  - `UserController` - CRUD operations
  - `UserProfileController` - Profile management
  - `RegisteredUserController` - Registration
  - `CustomOAuthController` - Social login
  
- **Models**
  - `User` - Main user model
  - `UserMeta` - Key-value metadata
  - `ConnectedAccount` - OAuth connections
  - `Role` - Spatie role
  - `Permission` - Spatie permission

- **Key Routes** (`routes/admin.php`, `routes/web.php`)
  ```php
  // Admin
  Route::resource('/users', UserController::class);
  Route::post('/users/{user}/suspend', [UserController::class, 'suspend']);
  Route::post('/users/{user}/unsuspend', [UserController::class, 'unsuspend']);
  
  // Frontend
  Route::get('/profile', [UserProfileController::class, 'show']);
  Route::get('/oauth/{provider}/callback', [CustomOAuthController::class, 'handleProviderCallback']);
  ```

- **Database Tables**
  - `users`
  - `user_metas`
  - `connected_accounts`
  - `roles`, `permissions`
  - `model_has_roles`, `role_has_permissions`

#### Frontend Implementation
- **Pages**
  - `Pages/Profile/Show.vue` - Profile display
  - `Pages/Profile/UpdateProfileInformationForm.vue`
  - `Pages/Profile/UpdatePasswordForm.vue`
  - `Pages/Profile/TwoFactorAuthenticationForm.vue`
  - `Pages/Profile/ConnectedAccountsForm.vue`
  - `Pages/User/Index.vue` - Admin user list
  - `Pages/User/Create.vue`, `Pages/User/Edit.vue`

- **Permissions**
  - `system.dashboard` - Access admin panel
  - `user.view`, `user.add`, `user.edit`, `user.delete`
  - `manageUserTrashed` - Manage deleted users
  - `public_page.profile` - Public profile access

---

### 📄 Content Management System (CMS)

#### Features
- **Page Builder**
  - Drag-and-drop component system
  - Component types: Header, Text, Image, Gallery, User List, Events Calendar, etc.
  - Translatable content (multi-language)
  - SEO settings per page
  - Custom CSS injection
  - Page templates and duplication
  
- **Blog System**
  - Post creation with categories
  - Featured images
  - Rich text editor (TinyMCE)
  - Multi-language translations
  - Category hierarchies (nested set)

- **Media Library**
  - Cloudinary integration
  - Image optimization
  - Image editor (crop, resize)
  - Metadata and descriptions
  - Media translations

#### Backend Implementation
- **Controllers**
  - `PageController` - Page CRUD
  - `PostController` - Blog posts
  - `CategoryController` - Categories
  - `MediaController` - Media management
  - `Frontend\PageController` - Public page display
  
- **Models**
  - `Page`, `PageTranslation`
  - `Post`
  - `Category`, `CategoryTranslation`
  - `Media`, `MediaTranslation`

- **Key Routes**
  ```php
  // Admin
  Route::resource('/pages', PageController::class);
  Route::post('/pages/duplicate/{page}', [PageController::class, 'duplicatePage']);
  Route::resource('/posts', PostController::class);
  Route::resource('/media', MediaController::class);
  
  // Frontend
  Route::get('/{page_translation}', [PageController::class, 'show']);
  Route::get(trans('routes.blog.show'), [PostController::class, 'show']);
  ```

- **Database Tables**
  - `pages`, `page_translations`
  - `posts`
  - `categories`, `category_translations`
  - `media`, `media_translations`
  - `category_post` (pivot)

#### Frontend Implementation
- **Admin Pages**
  - `Pages/Page/Index.vue`, `Pages/Page/Create.vue`, `Pages/Page/Edit.vue`
  - `Pages/Page/FormBuilder.vue` - Page builder interface
  - `Pages/Post/Index.vue`, `Pages/Post/Create.vue`, `Pages/Post/Edit.vue`
  - `Pages/Media/Index.vue`, `Pages/Media/Create.vue`

- **Frontend Pages**
  - Dynamic page rendering via blade templates
  - `themes/buskincity/` - Active theme assets

---

### 📅 Booking Module

#### Features
- **Event Booking**
  - Time-slot based bookings
  - Recurring events (schedules)
  - Capacity management
  - Price tiers
  - Booking confirmation
  - Reschedule/cancel functionality
  
- **Schedule Management**
  - Recurring rules (daily, weekly, monthly)
  - Time slots per day
  - Blackout dates
  - Capacity limits
  - Dynamic pricing

- **Order Management**
  - Order tracking
  - Check-in system (QR codes)
  - Order history
  - Cancellation handling
  - Payment integration (Stripe)

#### Backend Implementation
- **Controllers** (`modules/Booking/Http/Controllers/`)
  - `ProductController` - Bookable products/events
  - `ProductEventController` - Event-specific settings
  - `OrderController` - Admin order management
  - `Frontend\ProductController` - Public event browsing
  - `Frontend\OrderController` - User bookings
  
- **Entities** (`modules/Booking/Entities/`)
  - `Event` - Bookable events
  - `EventCalendar` - Calendar view
  - `Schedule` - Recurring schedule
  - `ScheduleRule` - Recurrence rules
  - `ScheduleRuleTime` - Time slots
  - `OrderCheckIn` - Check-in records

- **Routes** (`modules/Booking/Routes/web.php`)
  ```php
  // Admin
  Route::prefix('admin/booking')->group(function() {
      Route::resource('products', ProductController::class);
      Route::resource('orders', OrderController::class);
      Route::post('/orders/{order}/cancel');
      Route::post('/orders/{order}/reschedule');
  });
  
  // Frontend
  Route::prefix('booking')->group(function() {
      Route::get('products', [FrontendProductController::class, 'index']);
      Route::post('orders/{product}/book-event');
      Route::post('orders/{order}/check-in');
  });
  ```

- **Database Tables**
  - `events`
  - `schedules`, `schedule_rules`, `schedule_rule_times`
  - `order_check_ins`

#### Frontend Implementation
- **Admin Pages**
  - `modules/Booking/Resources/assets/js/Pages/Product/Index.vue`
  - `modules/Booking/Resources/assets/js/Pages/Product/Form.vue`
  - `modules/Booking/Resources/assets/js/Pages/Product/FormEvent.vue`
  - `modules/Booking/Resources/assets/js/Pages/Order/Index.vue`

- **Frontend Pages**
  - `modules/Booking/Resources/assets/js/Pages/Frontend/Product/Index.vue`
  - `modules/Booking/Resources/assets/js/Pages/Frontend/Product/Show.vue`
  - `modules/Booking/Resources/assets/js/Pages/Frontend/Order/Index.vue`

- **Key Components**
  - Event calendar widget
  - Time slot selector
  - Booking form
  - Check-in QR scanner

---

### 🏛️ Space Module

#### Features
- **Venue Management**
  - Space listings (performance venues)
  - Location-based search
  - Google Maps integration
  - Space types categorization
  - Manager assignments
  - Multi-language descriptions
  
- **Space Events**
  - Events happening at spaces
  - Event calendar integration
  - Public/private events
  - Event RSVP

- **Contact Forms**
  - Venue inquiry forms
  - Custom form fields

#### Backend Implementation
- **Controllers** (`modules/Space/Http/Controllers/`)
  - `SpaceController` - CRUD operations
  - `PageController` - Space-specific pages
  - `EventController` - Space events
  - `SpaceTypeController` - Space categorization
  - `Frontend\SpaceController` - Public space listings
  
- **Entities** (`modules/Space/Entities/`)
  - `Space`, `SpaceTranslation`
  - `SpaceEvent`, `SpaceEventTranslation`
  - `Page`, `PageTranslation` - Space-specific pages

- **Routes** (`modules/Space/Routes/web.php`)
  ```php
  // Admin
  Route::prefix('admin')->group(function() {
      Route::resource('spaces', SpaceController::class);
      Route::resource('spaces.events', EventController::class);
      Route::post('spaces/update-manager/{space}');
  });
  
  // Frontend
  Route::get('spaces', [FrontendSpaceController::class, 'index']);
  Route::get('spaces/{slugs}', [FrontendSpaceController::class, 'show']);
  ```

- **Database Tables** (in module migrations)
  - Space-related tables
  - Space types
  - Space events

#### Frontend Implementation
- **Admin Pages**
  - `modules/Space/Resources/assets/js/Pages/Space/Index.vue`
  - `modules/Space/Resources/assets/js/Pages/Space/Form.vue`
  - `modules/Space/Resources/assets/js/Pages/Event/Form.vue`

- **Frontend Pages**
  - `modules/Space/Resources/assets/js/Pages/Frontend/Index.vue`
  - `modules/Space/Resources/assets/js/Pages/Frontend/Show.vue`

---

### 📝 Form Builder Module

#### Features
- **Dynamic Form Creation**
  - Drag-and-drop form builder
  - Field types: Text, Textarea, Select, Checkbox, Radio, File, etc.
  - Field validation rules
  - Conditional logic
  - Multi-page forms
  
- **Form Submissions**
  - Entry storage
  - Email notifications
  - Export to Excel
  - Form analytics
  
- **Form Mapping**
  - Map form fields to user fields
  - Auto-update user profiles
  - Custom field mapping rules

#### Backend Implementation
- **Controllers** (`modules/FormBuilder/Http/Controllers/`)
  - `FormController` - Form CRUD
  - `FormEntryController` - Submissions
  - `FormMappingController` - Field mapping rules
  
- **Entities**
  - `Form` - Form definition
  - `FormEntry` - Submissions
  - `FormMappingRule` - Field mappings
  - `FormNotificationSetting` - Email settings
  - `FieldGroup` - Field grouping

- **Routes** (`modules/FormBuilder/Routes/web.php`)
  ```php
  // Admin
  Route::resource('forms', FormController::class);
  Route::get('forms/{form}/entries');
  
  // Public
  Route::post('forms/save', [FormController::class, 'submit']);
  ```

#### Frontend Implementation
- **Admin Pages**
  - `modules/FormBuilder/Resources/assets/js/Pages/Form/Index.vue`
  - `modules/FormBuilder/Resources/assets/js/Pages/Form/Builder.vue`
  - `modules/FormBuilder/Resources/assets/js/Pages/Entry/Index.vue`

---

### 💳 E-commerce (Lunar Integration)

#### Features
- **Product Management** (via Lunar PHP)
  - Product variants
  - Pricing
  - Inventory
  - Categories
  
- **Order Processing**
  - Cart management
  - Checkout flow
  - Payment processing (Stripe)
  - Order fulfillment

#### Backend Implementation
- Uses Lunar PHP core (`lunarphp/core`)
- Extended in `modules/Ecommerce/`
- Stripe integration for payments

---

### 🎨 Theme System

#### Features
- **Multi-theme Support**
  - Active theme: `buskincity`
  - Theme configuration via `.env`: `THEME_ACTIVE=buskincity`
  
- **Theme Customization**
  - Color schemes (admin settings)
  - Font selection (Google Fonts)
  - Header/footer layouts
  - Navigation menu builder
  - Custom CSS injection
  
- **SEO Settings**
  - Meta tags
  - Open Graph
  - Twitter Cards
  - Sitemap generation

#### Backend Implementation
- **Controllers**
  - `ThemeColorController`
  - `ThemeFontController`
  - `ThemeHeaderController`, `ThemeFooterController`
  - `ThemeHeaderNavigationController`, `ThemeFooterNavigationController`
  - `ThemeSeoController`
  - `ThemeAdvanceController`

- **Theme Storage**
  - Theme files: `themes/buskincity/`
  - Settings stored in `settings` table
  - Generated CSS: `public/css/stored/`

#### Frontend Implementation
- **Admin Pages**
  - `Pages/Theme/Color.vue`
  - `Pages/Theme/Font.vue`
  - `Pages/Theme/Header.vue`
  - `Pages/Theme/Footer.vue`
  - `Pages/Theme/Seo.vue`
  - `Pages/Theme/Advance.vue`

---

### 🌐 Multi-Language System

#### Features
- **Language Management**
  - Add/edit languages
  - Set default language
  - Flag icons
  - RTL support
  
- **Translation Management**
  - Database-driven translations
  - Translation import/export
  - Key-value translation system
  - Translation editor UI
  
- **Translatable Models**
  - Pages, Posts, Categories
  - Media, Spaces, Events
  - Products

#### Backend Implementation
- **Packages**
  - `mcamara/laravel-localization` - Route localization
  - `astrotomic/laravel-translatable` - Model translations
  - `spatie/laravel-translation-loader` - DB translations

- **Controllers**
  - `LanguageController` - Language settings
  - `TranslationManagerController` - Translation CRUD
  - `ChangeLanguageController` - Switch language

- **Routes**
  ```php
  Route::prefix('{locale}')->group(function() {
      // All localized routes
  });
  ```

- **Database Tables**
  - `languages`
  - `translations`
  - `*_translations` (page_translations, post_translations, etc.)

#### Frontend Implementation
- Language switcher in header
- `Pages/Language.vue` - Language settings
- `Pages/TranslationManager/Index.vue`

---

### 💰 Payment & Donations

#### Features
- **Stripe Integration**
  - Connected accounts (for performers)
  - Payment processing
  - Webhook handling
  - Subscription support
  
- **Donations**
  - One-time donations to performers
  - Custom amounts
  - Success/thank you pages

#### Backend Implementation
- **Controllers**
  - `StripeController` - Stripe settings & account management
  - `Frontend\DonationController` - Donation flow
  - `WebhookStripeController` - Stripe webhooks
  - `Frontend\StripeController` - Performer account setup

- **Routes**
  ```php
  // Admin Stripe settings
  Route::get('settings/stripe', [StripeController::class, 'edit']);
  
  // Performer Stripe account
  Route::post('payments/stripe/create-connected-account');
  Route::get('payments/stripe/return');
  
  // Donations
  Route::post('donations/checkout/{user}', [DonationController::class, 'checkout']);
  Route::get('donations/{user}/success', [DonationController::class, 'success']);
  
  // Webhooks
  Route::post('webhooks/stripe', WebhookStripeController::class);
  ```

- **Database Tables**
  - `payment_webhooks`

---

### 📊 System Management

#### Features
- **Module Management**
  - Enable/disable modules
  - Module configuration
  - Module navigation settings
  - Module activation confirmation
  
- **Settings Management**
  - Site settings
  - API keys (Google Maps, reCAPTCHA, Cloudinary, TinyMCE)
  - Email settings
  - Cookie consent
  
- **System Logs**
  - Activity log (Telescope)
  - Error log tracking
  - User action logging

- **Error Handling**
  - Custom error log model
  - Admin error log viewer
  - Bulk delete errors

#### Backend Implementation
- **Controllers**
  - `ModuleController` - Module management
  - `SettingKeyController` - API key management
  - `SystemLogController` - Activity logs (Telescope)
  - `ErrorLogController` - Error management
  - `CookieConsentController` - Cookie settings

- **Models**
  - `Module`
  - `Setting`
  - `ErrorLog`

- **Database Tables**
  - `modules`
  - `settings`
  - `error_logs`

#### Frontend Implementation
- **Admin Pages**
  - `Pages/Module/Index.vue`
  - `Pages/Module/Edit.vue`
  - `Pages/Setting/Keys.vue`
  - `Pages/SystemLog/Index.vue`
  - `Pages/ErrorLog/Index.vue`

---

## Database Schema Analysis

### Core Tables

#### Users & Authentication
```
users
├── id (PK)
├── first_name, last_name
├── email (unique)
├── password (nullable - for OAuth users)
├── email_verified_at
├── profile_photo_media_id (FK → media)
├── language_id (FK → languages)
├── country_code (FK → countries.alpha2)
├── is_suspended (boolean)
├── unique_key (for QR codes)
├── two_factor_secret, two_factor_recovery_codes
├── created_at, updated_at, deleted_at (soft deletes)

user_metas
├── id (PK)
├── user_id (FK → users)
├── key
├── value (text)

connected_accounts
├── id (PK)
├── user_id (FK → users)
├── provider (google, facebook, github)
├── provider_id
├── token, secret, refresh_token
├── expires_at
```

#### Roles & Permissions (Spatie)
```
roles
├── id (PK)
├── name (Super Administrator, Administrator, Performer)
├── guard_name
├── created_at, updated_at

permissions
├── id (PK)
├── name (system.dashboard, user.add, etc.)
├── guard_name
├── created_at, updated_at

role_has_permissions (pivot)
├── permission_id (FK)
├── role_id (FK)

model_has_roles (polymorphic pivot)
├── role_id (FK)
├── model_type (App\Models\User)
├── model_id

model_has_permissions (polymorphic pivot)
├── permission_id (FK)
├── model_type
├── model_id
```

#### Content Management
```
pages
├── id (PK)
├── author_id (FK → users)
├── slug
├── created_at, updated_at, deleted_at

page_translations
├── id (PK)
├── page_id (FK → pages)
├── locale (en, es, etc.)
├── title
├── content (JSON - page builder data)
├── meta_title, meta_description
├── status (published, draft)
├── published_at

posts
├── id (PK)
├── author_id (FK → users)
├── slug
├── title, content
├── featured_image_id (FK → media)
├── status
├── published_at
├── created_at, updated_at, deleted_at

categories
├── id (PK)
├── _lft, _rgt, parent_id (nested set)

category_translations
├── id (PK)
├── category_id (FK)
├── locale
├── name, slug

category_post (pivot)
├── post_id (FK)
├── category_id (FK)

media
├── id (PK)
├── author_id (FK → users)
├── medially_type, medially_id (polymorphic)
├── cloudinary_public_id
├── file_url
├── file_size, mime_type
├── width, height
├── created_at, updated_at, deleted_at

media_translations
├── id (PK)
├── media_id (FK)
├── locale
├── title, alt, description

mediables (polymorphic pivot)
├── media_id (FK)
├── mediable_type
├── mediable_id
```

#### Localization
```
languages
├── id (PK)
├── name
├── code (en, es, fr)
├── flag
├── is_default
├── is_active
├── script (latin, arabic)
├── direction (ltr, rtl)
├── created_at, updated_at

translations
├── id (PK)
├── group (routes, validation, etc.)
├── key
├── text (JSON - {en: "value", es: "valor"})
├── created_at, updated_at
```

#### Settings & Configuration
```
settings
├── id (PK)
├── key
├── value (text or JSON)
├── created_at, updated_at

modules
├── id (PK)
├── name (Booking, Space, etc.)
├── is_active
├── order
├── settings (JSON)
├── created_at, updated_at

global_options
├── id (PK)
├── key
├── value (text)
├── created_at, updated_at
```

#### Navigation
```
menus
├── id (PK)
├── slug (header, footer)
├── created_at, updated_at

menu_items
├── id (PK)
├── menu_id (FK)
├── _lft, _rgt, parent_id (nested set)
├── type (page, url, external)
├── page_id (FK → pages, nullable)
├── label (JSON - multilang)
├── url
├── target (_self, _blank)
├── order
├── created_at, updated_at
```

#### Booking Module Tables
```
events (in Booking module)
├── id (PK)
├── product_id (FK → products)
├── schedule_id (FK → schedules)
├── ... (event-specific fields)

schedules
├── id (PK)
├── ... (recurring schedule definition)

schedule_rules
├── id (PK)
├── schedule_id (FK)
├── frequency (daily, weekly, monthly)
├── interval
├── ... (recurrence rules)

schedule_rule_times
├── id (PK)
├── schedule_rule_id (FK)
├── start_time
├── end_time

order_check_ins
├── id (PK)
├── order_id (FK)
├── checked_in_at
```

#### Space Module Tables
```
spaces (in Space module)
├── id (PK)
├── slug
├── ... (space details)

space_translations
├── id (PK)
├── space_id (FK)
├── locale
├── name, description
├── ...

space_events
├── id (PK)
├── space_id (FK)
├── ... (event details)

space_event_translations
├── id (PK)
├── space_event_id (FK)
├── locale
├── title, description
```

#### FormBuilder Module Tables
```
forms (in FormBuilder module)
├── id (PK)
├── name
├── fields (JSON - form schema)
├── settings (JSON)
├── created_at, updated_at

form_entries
├── id (PK)
├── form_id (FK)
├── data (JSON - submission data)
├── user_id (FK, nullable)
├── created_at

form_mapping_rules
├── id (PK)
├── form_id (FK)
├── form_field
├── user_field
├── mapping_type

form_notification_settings
├── id (PK)
├── form_id (FK)
├── email_to
├── subject
├── message_template
```

### Key Relationships

#### Polymorphic Relationships
1. **Mediable** - Any model can have media
   - `User` → `media` (profile photos, etc.)
   - `Post` → `media` (featured images)
   - `Space` → `media` (gallery)

2. **Model Has Roles/Permissions** (Spatie)
   - `User` → `roles`
   - `User` → `permissions` (direct)

#### One-to-Many
- `User` → `pages` (author)
- `User` → `posts` (author)
- `User` → `media` (uploaded by)
- `Language` → `users` (origin language)
- `Country` → `users`
- `Page` → `page_translations`
- `Post` → `category_post` → `categories`

#### Many-to-Many
- `posts` ↔ `categories` (via `category_post`)
- `roles` ↔ `permissions` (via `role_has_permissions`)
- `users` ↔ `roles` (via `model_has_roles`)

#### Nested Set (Hierarchies)
- `categories` - Uses `kalnoy/nestedset` (_lft, _rgt, parent_id)
- `menu_items` - Nested navigation

---

## Data Flow Examples

### Example 1: User Registration Flow

```
1. User submits registration form
   ↓
2. Frontend (Pages/Auth/Register.vue)
   → Inertia.post('/register', formData)
   ↓
3. Route (web.php)
   POST /register → RegisteredUserController@store
   ↓
4. Controller validates input
   - reCAPTCHA verification
   - Validation rules
   ↓
5. UserService creates user
   - Hash password
   - Generate unique_key
   - Assign default role (via RoleSeeder)
   ↓
6. User model saved to database
   - UserObserver fires
   - Creates default UserMetas
   - Sends verification email
   ↓
7. Response redirected to dashboard
   ↓
8. Frontend displays dashboard
```

### Example 2: Creating a Page with Page Builder

```
1. Admin navigates to /admin/pages/create
   ↓
2. Inertia loads Pages/Page/Create.vue
   - Fetches form data (languages, media library)
   ↓
3. Admin builds page using FormBuilder.vue component
   - Drags components (Header, Text, Gallery, etc.)
   - Configures component settings (text, images, styles)
   - Adds translations for each language
   ↓
4. Admin submits form
   → Inertia.post('/admin/pages', pageData)
   ↓
5. Route → PageController@store
   ↓
6. Controller processes:
   - Validates page data
   - PageService creates Page record
   - Creates PageTranslation for each language
   - Processes page_builder_components (JSON)
   - Associates media via MediaService
   ↓
7. PageObserver fires
   - Generates CSS for page (StylePageBuilderController)
   - Clears cache
   ↓
8. Database saves:
   - pages table (base record)
   - page_translations (for each locale)
   - mediables (if media attached)
   ↓
9. Response redirects to /admin/pages
   - Flash message shown
   ↓
10. Frontend updates page list
```

### Example 3: Event Booking Flow

```
1. User browses events at /booking/products
   ↓
2. Inertia loads Booking::Frontend/Product/Index.vue
   - Fetches available products with event schedules
   ↓
3. User selects event and date
   → Frontend requests available times
   → API: GET /booking/products/{product}/available-times/{date}
   ↓
4. Backend (ProductController@availableTimes):
   - ScheduleService calculates available slots
   - Checks capacity, existing bookings
   - Returns time slots
   ↓
5. User selects time and quantity
   → Inertia.post('/booking/orders/{product}/book-event', bookingData)
   ↓
6. Backend (OrderController@bookEvent):
   - Validates availability
   - Creates Order (via Ecommerce module)
   - Associates with Event
   - Processes payment (Stripe)
   ↓
7. Database saves:
   - orders table
   - order_items
   - Updates event capacity
   ↓
8. Payment processed:
   - Stripe checkout session created
   - User redirected to Stripe
   ↓
9. Stripe webhook fires on success
   → WebhookStripeController
   - Updates order status
   - Sends confirmation email
   ↓
10. User redirected to success page
    - Shows booking confirmation
    - Generates QR code for check-in
```

---

## API Documentation

### Internal API Routes (Admin)

All API routes are prefixed with `/api/` and protected by `auth:sanctum`, `verified` middleware, and rate-limited with `throttle:api`.

#### Media Upload
```
POST /api/media
Headers: 
  - Authorization: Bearer {token}
  - Content-Type: multipart/form-data
Body:
  - file: (file)
  - alt: (string, optional)
  - title: (string, optional)
Response: { id, file_url, ... }
```

#### Page Builder Options
```
GET /api/page-builder/type-options
Response: { types: [ ... ] }

GET /api/page-builder/user-list/role-options
Response: { roles: [ {id, name}, ... ] }

GET /api/page-builder/post/category-options
Response: { categories: [ ... ] }
```

#### Form Options
```
GET /api/options/phone-countries
Response: { countries: [ ... ] }

GET /api/options/countries
Response: { countries: [ ... ] }

GET /api/options/timezones
Response: { timezones: [ ... ] }
```

#### Widget Data
```
GET /api/widget/latest-registrations
Response: { users: [ ... ] }

GET /api/widget/data/{uuid}
Response: { data: { ... } }
```

#### Settings
```
GET /api/setting/max-file-size
Response: { max_size: 10485760 } // in bytes
```

### Public API Routes

#### Page Builder Components
```
GET /api/page-builder/components/user-list
Query Parameters:
  - roles: array of role IDs
  - limit: integer
  - page: integer
Response: { users: [ ... ], pagination: { ... } }
```

#### Booking API
```
GET /api/booking/events-calendar
Query Parameters:
  - start: date (YYYY-MM-DD)
  - end: date (YYYY-MM-DD)
  - location: space ID (optional)
Response: { events: [ ... ] }

GET /api/booking/events-calendar/location-options
Response: { locations: [ ... ] }

GET /api/booking/upcoming-events/{userUniqueKey}
Response: { events: [ ... ] }
```

#### Space API
```
GET /api/space/space-events/{encryptedSpaceId}
Response: { events: [ ... ] }
```

### Webhooks

#### Stripe Webhook
```
POST /webhooks/stripe
Headers:
  - Stripe-Signature: {signature}
Body: (Stripe event payload)
Events handled:
  - payment_intent.succeeded
  - checkout.session.completed
  - account.updated
  - ... (other Stripe events)
```

---

## Setup & Configuration Guide

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js & NPM/Yarn
- PostgreSQL 12+
- Redis (optional but recommended)
- Docker (recommended)

### Local Development Setup

#### 1. Clone and Configure
```bash
cd /path/to/project
cp .env.example .env
cp .env.dusk.local.example .env.dusk.local
cp .env.sail.example .env.sail
```

#### 2. Configure Environment
Edit `.env`:
```env
APP_NAME=BuskinCity
APP_ENV=local
APP_URL=http://localhost:8000
APP_DOMAIN=localhost

DB_CONNECTION=pgsql
DB_HOST=localhost    # IMPORTANT: Use localhost for dev
DB_PORT=5432
DB_DATABASE=buskincity
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# External Services
CLOUDINARY_URL=cloudinary://...
CLOUDINARY_UPLOAD_PRESET=your_preset
GOOGLE_MAPS_API_KEY=your_key
RECAPTCHA_SITE_KEY=your_key
RECAPTCHA_SECRET_KEY=your_secret
TINYMCE_API_KEY=your_key

# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...

# OAuth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

THEME_ACTIVE=buskincity
```

#### 3. Install Dependencies
```bash
# Using Docker Sail (recommended)
composer install  # May show error - expected
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate

# Or without Docker
composer install
php artisan key:generate
```

#### 4. Database Setup
**⚠️ WARNING**: Only run migrations if `DB_HOST=localhost`!

```bash
# With Sail
./vendor/bin/sail artisan migrate:fresh --seed

# Without Sail
php artisan migrate:fresh --seed
```

Seeders will create:
- Default users (Super Admin, Admin, Performers)
- Roles and permissions
- Languages (English, Spanish, etc.)
- Sample content
- Modules configuration

#### 5. Frontend Assets
```bash
# With Sail
./vendor/bin/sail yarn install
./vendor/bin/sail yarn build  # or yarn dev for development

# Without Sail
yarn install
yarn build  # or yarn dev
```

#### 6. Clear Caches
```bash
./vendor/bin/sail artisan optimize:clear
# or
php artisan optimize:clear
```

#### 7. Access Application
- **Frontend**: http://localhost:8000
- **Admin**: http://localhost:8000/admin/login
  - Email: admin@example.com
  - Password: (check UserAndPermissionSeeder)

### Production Deployment

#### 1. Server Requirements
- PHP 8.1+ with extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD
- PostgreSQL 12+
- Redis
- Nginx or Apache with mod_rewrite
- SSL certificate

#### 2. Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_DOMAIN=yourdomain.com

# Use strong keys!
APP_KEY=       # Generate with: php artisan key:generate

# Production database
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_DATABASE=production_db
DB_USERNAME=prod_user
DB_PASSWORD=strong_password

# Production cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Telescope (disable in production or protect carefully)
TELESCOPE_ENABLED=false
```

#### 3. Deployment Steps
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
yarn install
yarn build

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Restart queue workers
php artisan queue:restart
```

#### 4. Web Server Configuration (Nginx)
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /path/to/public;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 5. Queue Worker (Supervisor)
```ini
[program:buskincity-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

#### 6. Scheduled Tasks (Cron)
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### API Keys & Third-Party Setup

#### Cloudinary
1. Sign up at cloudinary.com
2. Get your cloud URL from dashboard
3. Create upload preset (unsigned or signed)
4. Add to `.env`:
   ```
   CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
   CLOUDINARY_UPLOAD_PRESET=your_preset
   ```

#### Google Maps
1. Create project in Google Cloud Console
2. Enable Maps JavaScript API, Geocoding API
3. Create API key with restrictions
4. Add to `.env`:
   ```
   GOOGLE_MAPS_API_KEY=your_key
   ```

#### reCAPTCHA
1. Register at google.com/recaptcha
2. Get site key and secret
3. Add to `.env`:
   ```
   RECAPTCHA_SITE_KEY=your_site_key
   RECAPTCHA_SECRET_KEY=your_secret_key
   ```

#### Stripe
1. Create Stripe account
2. Get API keys from dashboard
3. Set up webhook endpoint: `https://yourdomain.com/webhooks/stripe`
4. Add to `.env`:
   ```
   STRIPE_KEY=pk_...
   STRIPE_SECRET=sk_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

#### TinyMCE
1. Sign up at tiny.cloud
2. Get API key
3. Add to `.env`:
   ```
   TINYMCE_API_KEY=your_key
   ```

---

## Code Quality & Testing

### Testing Framework
- **PHPUnit 9.3.3** - Unit and feature tests
- **Laravel Dusk 7.7** - Browser tests
- Test files: `tests/` directory (85 test files)

### Running Tests
```bash
# PHPUnit tests
./vendor/bin/sail artisan test
# or
php artisan test

# Dusk browser tests
./vendor/bin/sail dusk
# or
php artisan dusk
```

### Code Organization Patterns

#### Service Classes
Located in `app/Services/`, handle business logic:
- Single Responsibility Principle
- Dependency Injection
- Testable methods

Example:
```php
class MediaService
{
    public function upload(UploadedFile $file, string $folder, StorageInterface $storage)
    {
        // Upload logic
    }
    
    public function destroy(Media $media, StorageInterface $storage)
    {
        // Deletion logic
    }
}
```

#### Repository Pattern (Implicit)
Models use query scopes:
```php
// Instead of repositories, use scopes
User::search($term)
    ->inRoles([1, 2])
    ->backend()
    ->available()
    ->paginate();
```

#### Policy Authorization
```php
// UserPolicy.php
public function update(User $authUser, User $user)
{
    return $authUser->can('user.edit') && $authUser->id !== $user->id;
}

// In controller
$this->authorize('update', $user);
```

#### Form Requests
Validation in dedicated request classes:
```php
// app/Http/Requests/
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|unique:users',
            // ...
        ];
    }
}
```

### Code Quality Observations

#### Strengths
1. **Modular Architecture** - Clear separation of concerns via modules
2. **Service Layer** - Business logic extracted from controllers
3. **Policy-Based Auth** - Fine-grained authorization
4. **Translatable Models** - Proper multi-language support
5. **Type Hinting** - Good use of PHP 8.1 type declarations
6. **Middleware** - Custom middleware for module verification, recaptcha, etc.

#### Areas for Improvement
1. **Testing Coverage** - Could benefit from more feature tests
2. **API Documentation** - No OpenAPI/Swagger specs
3. **Frontend Type Safety** - Consider TypeScript for Vue components
4. **Caching Strategy** - More aggressive caching for page builder
5. **Job Queues** - Some operations (email, media processing) should be queued

### Security Considerations

#### Implemented
- ✅ CSRF protection (Laravel default)
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade escaping)
- ✅ Password hashing (bcrypt)
- ✅ Rate limiting (API, login, registration)
- ✅ reCAPTCHA on forms
- ✅ Email verification
- ✅ Two-factor authentication
- ✅ Soft deletes for users
- ✅ Role-based access control
- ✅ Content Security Policy headers (configurable)
- ✅ Signed URLs for sensitive actions
- ✅ Input validation and sanitization

#### Recommended Additions
- [ ] Security headers middleware (Helmet-like)
- [ ] API request signing for webhooks
- [ ] File upload virus scanning
- [ ] Rate limiting per user (not just IP)
- [ ] Audit logging for sensitive operations
- [ ] Environment-specific error messages

---

## Recommendations & Best Practices

### Immediate Improvements

1. **Add TypeScript to Vue Components**
   - Install: `vue-tsc`, `@types/node`
   - Create `tsconfig.json`
   - Rename `.vue` to `.vue` with `<script lang="ts">`
   - Benefits: Type safety, better IDE support

2. **Implement API Documentation**
   - Install `darkaonline/l5-swagger`
   - Document all API endpoints with OpenAPI 3.0
   - Generate interactive docs at `/api/documentation`

3. **Queue Background Jobs**
   ```php
   // Convert to queued jobs:
   - Email sending (VerifyEmail, ResetPassword)
   - Media optimization (image resizing)
   - PDF generation (invoices)
   - External API calls
   ```

4. **Add Response Caching**
   ```php
   // Cache expensive queries:
   - Page builder rendered output
   - Menu structures
   - Translation strings
   - Public profiles
   ```

5. **Frontend State Management**
   - Consider Pinia for complex state
   - Centralize form validation logic
   - Implement global error handling

### Performance Optimization

1. **Database Indexing**
   ```sql
   -- Recommended indexes:
   CREATE INDEX idx_users_email ON users(email);
   CREATE INDEX idx_pages_slug ON pages(slug);
   CREATE INDEX idx_page_translations_locale ON page_translations(locale);
   CREATE INDEX idx_media_cloudinary_public_id ON media(cloudinary_public_id);
   ```

2. **Eager Loading**
   ```php
   // Prevent N+1 queries:
   Page::with(['translations', 'media', 'author'])->get();
   User::with(['roles.permissions', 'profilePhoto'])->get();
   ```

3. **Redis Caching**
   ```php
   // Cache frequently accessed data:
   Cache::remember('settings', 3600, fn() => Setting::all());
   Cache::remember("page.{$slug}.{$locale}", 3600, fn() => ...);
   ```

4. **Asset Optimization**
   - Enable Vite build optimization
   - Lazy load Vue components
   - Image lazy loading (already using lazysizes)
   - CDN for static assets

### Code Quality

1. **Add PHPStan**
   ```bash
   composer require --dev phpstan/phpstan
   ./vendor/bin/phpstan analyse app modules
   ```

2. **ESLint for Vue**
   Already configured in `.eslintrc.js` - ensure it's enforced in CI/CD

3. **Pre-commit Hooks**
   ```bash
   composer require --dev brainmaestro/composer-git-hooks
   # Run: PHP CS Fixer, PHPStan, ESLint, tests
   ```

4. **Automated Testing in CI**
   Set up GitHub Actions or GitLab CI:
   ```yaml
   - Run PHPUnit tests
   - Run Dusk tests
   - Check code style
   - Build frontend assets
   ```

### Scalability

1. **Horizontal Scaling Preparation**
   - Ensure sessions are in Redis (already configured)
   - Use S3/Cloudinary for media (already using Cloudinary)
   - Separate queue workers
   - Load balancer ready (stateless app)

2. **Database Read Replicas**
   ```php
   // In database config:
   'read' => [
       'host' => env('DB_READ_HOST', '127.0.0.1'),
   ],
   'write' => [
       'host' => env('DB_WRITE_HOST', '127.0.0.1'),
   ]
   ```

3. **CDN Integration**
   - Serve static assets via CDN
   - Cache API responses at edge
   - Consider Cloudflare for DDoS protection

### Monitoring & Logging

1. **Application Monitoring**
   - Install Sentry for error tracking
   - New Relic or Datadog for APM
   - Telescope already installed for dev

2. **Log Aggregation**
   - Use Laravel's logging channels
   - Send to centralized logging (ELK, Papertrail)
   - Alert on critical errors

3. **Metrics Dashboard**
   - User registration trends
   - Booking conversion rates
   - Payment success/failure rates
   - Page load times

### Documentation

1. **Code Documentation**
   - PHPDoc for all public methods
   - Generate docs with `phpDocumentor`

2. **User Documentation**
   - Admin user guide
   - Performer onboarding guide
   - API consumer guide

3. **Developer Documentation**
   - Architecture decision records (ADRs)
   - Module development guide
   - Contribution guidelines

---

## Module Development Guide

### Creating a New Module

```bash
php artisan module:make ModuleName
```

Structure:
```
modules/ModuleName/
├── Config/
│   └── config.php
├── Database/
│   ├── Migrations/
│   ├── Seeders/
│   └── factories/
├── Entities/              # Models
├── Http/
│   ├── Controllers/
│   └── Middleware/
├── Providers/
│   ├── ModuleNameServiceProvider.php
│   └── RouteServiceProvider.php
├── Resources/
│   └── assets/
│       └── js/
│           └── Pages/     # Inertia Vue pages
├── Routes/
│   ├── api.php
│   └── web.php
├── Services/              # Business logic
├── Policies/
├── Widgets/
└── module.json
```

### Module Registration

1. **module.json**
```json
{
    "name": "ModuleName",
    "alias": "modulename",
    "description": "",
    "keywords": [],
    "priority": 0,
    "providers": [
        "Modules\\ModuleName\\Providers\\ModuleNameServiceProvider"
    ]
}
```

2. **Service Provider**
```php
class ModuleNameServiceProvider extends ServiceProvider
{
    protected $moduleName = 'ModuleName';
    protected $moduleNameLower = 'modulename';

    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
```

3. **Routes** (modules/ModuleName/Routes/web.php)
```php
Route::prefix('admin/modulename')
    ->name('admin.modulename.')
    ->middleware(['auth:sanctum','verified', 'verifyModule:ModuleName'])
    ->group(function() {
        Route::resource('resources', ResourceController::class);
    });
```

4. **Inertia Pages Resolution** (app.js)
```javascript
// Already handles module pages:
if (module[0] == 'ModuleName') {
    return resolvePageComponent(
        `../../modules/ModuleName/Resources/assets/js/Pages/${module[1]}.vue`,
        import.meta.glob(`../../modules/ModuleName/Resources/assets/js/Pages/**/*.vue`)
    );
}
```

### Module Middleware

Use `verifyModule` middleware:
```php
Route::middleware('verifyModule:ModuleName')->group(function() {
    // Routes only accessible if ModuleName module is active
});

// OR condition (Booking OR Space)
Route::middleware('verifyModule:Booking,Space,OR')->group(function() {
    // Routes if either module is active
});
```

---

## Conclusion

BuskinCity is a well-architected, feature-rich platform that successfully combines modern Laravel backend practices with a reactive Vue.js frontend through Inertia.js. Its modular design allows for easy feature additions and maintenance, while the comprehensive permission system and multi-language support make it suitable for international deployment.

The platform demonstrates strong adherence to Laravel conventions, clean code organization, and thoughtful separation of concerns. With the recommended improvements, particularly around testing, API documentation, and performance optimization, BuskinCity has solid potential for scalability and long-term maintainability.

### Key Strengths
- ✅ Modular architecture for feature isolation
- ✅ Comprehensive role-based permission system
- ✅ Multi-language support with translatable models
- ✅ Modern frontend with Inertia.js SPA experience
- ✅ Payment processing with Stripe Connected Accounts
- ✅ Flexible page builder for dynamic content
- ✅ Strong authentication & authorization
- ✅ Well-organized codebase following Laravel best practices

### Next Steps for Development Team
1. Implement comprehensive test suite
2. Add API documentation (OpenAPI/Swagger)
3. Set up CI/CD pipeline
4. Implement queue workers for background jobs
5. Add monitoring and error tracking
6. Document module development workflow
7. Create user and admin guides

---

**Document Version**: 1.0  
**Last Updated**: 2025-11-25  
**Laravel Version**: 9.x  
**Vue Version**: 3.2.2  
**Inertia Version**: 1.0.2
