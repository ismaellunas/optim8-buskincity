<?php

use App\Enums\PublishingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Entities\ProductVariant;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Ecommerce\ModuleService;
use Modules\Space\Entities\Space;

return new class extends Migration
{
    private $viewName = 'event_calendars';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lunarPrefix =  ModuleService::tablePrefix();

        $bookedEventQuery = "
            SELECT
                'booked_event' AS type,
                lo.id,
                lo.user_id,
                users.first_name || ' ' || users.last_name AS title,
                (SELECT value FROM user_metas WHERE user_id = users.id AND key = 'stage_name') AS titla_alt,
                users.profile_photo_media_id AS photo_media_id,
                events.booked_at AS started_at,
                events.booked_at AS ended_at,
                events.duration,
                events.duration_unit,
                schedules.timezone,
                sub_loc.location::json#>>'{0,address}' AS address,
                sub_loc.location::json#>>'{0,city}' AS city,
                sub_loc.location::json#>>'{0,country_code}' AS country_code,
                json_build_object('latitude', (sub_loc.location::json#>>'{0,latitude}')::double precision, 'longitude', (sub_loc.location::json#>>'{0,longitude}')::double precision) AS geolocation,
                json_build_object('event_id', events.id) AS entity_ids
            FROM {$lunarPrefix}order_lines AS lol
            JOIN {$lunarPrefix}orders AS lo ON lo.id = lol.order_id
            JOIN users ON users.id = lo.user_id
            JOIN events ON events.order_line_id = lol.id
            JOIN (SELECT max(id) AS id, order_line_id FROM events GROUP BY order_line_id) sub ON sub.id = events.id
            JOIN schedules ON schedules.id = events.schedule_id
            JOIN {$lunarPrefix}product_variants lpv ON lpv.id = lol.purchasable_id
                AND lol.purchasable_type = '".ProductVariant::class."'
            JOIN {$lunarPrefix}products lp ON lp.id = lpv.product_id
                AND lp.status = '".ProductStatus::PUBLISHED->value."'
            LEFT JOIN (SELECT lpm.product_id, lpm.value AS location from {$lunarPrefix}products_meta lpm WHERE lpm.key = 'locations') sub_loc ON sub_loc.product_id = lp.id
            WHERE lol.type = 'event'
                AND events.status IN ('".BookingStatus::UPCOMING->value."','".BookingStatus::ONGOING->value."')
        ";

        $spaceEventQuery = "
            SELECT DISTINCT ON (se.id)
                'space_event' AS type,
                se.id,
                NULL AS user_id,
                se.title AS title,
                NULL AS title_alt,
                m.media_id AS photo_media_id,
                se.started_at AS started_at,
                se.ended_at AS ended_at,
                NULL AS duration,
                NULL AS duration_unit,
                se.timezone AS timezone,
                CASE se.is_same_address_as_parent WHEN true THEN s.address ELSE se.address END AS address,
                CASE se.is_same_address_as_parent WHEN true THEN s.city ELSE se.city END AS city,
                CASE se.is_same_address_as_parent WHEN true THEN s.country_code ELSE se.country_code END AS country_code,
                CASE se.is_same_address_as_parent
                    WHEN true THEN json_build_object('latitude', s.latitude::double precision, 'longitude', s.longitude::double precision)
                    ELSE json_build_object('latitude', se.latitude::double precision, 'longitude', se.longitude::double precision)
                END AS geolocation,
                json_build_object('space_id', s.id) AS entity_ids
            FROM space_events AS se
            JOIN spaces s ON s.id = se.space_id
            LEFT JOIN mediables m ON m.mediable_id = s.id AND m.mediable_type = '".Space::class."' AND m.type = 'logo'
            WHERE se.status = '".PublishingStatus::PUBLISHED->value."'
        ";

        DB::statement(
            "CREATE OR REPLACE VIEW {$this->viewName} AS ("
                .$bookedEventQuery
                ." UNION ALL "
                .$spaceEventQuery
            .")"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS {$this->viewName}");
    }
};
