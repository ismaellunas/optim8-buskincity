-- FIX: Recreate the event_calendars view (post ProductEvent removal, T8.5)
-- Matches database/migrations/2026_06_04_100000_update_event_calendars_view_for_search_and_pins.php
-- ProductEvent rows are no longer included — booked performer events use the booked_event arm.

DROP VIEW IF EXISTS event_calendars;

CREATE VIEW event_calendars AS (
    -- BOOKED EVENTS (performer bookings on published pitches)
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
        COALESCE(
            country_booked.alpha2,
            sub_loc.location::json#>>'{0,country_code}'
        ) AS country_code,
        json_build_object(
            'latitude', (sub_loc.location::json#>>'{0,latitude}')::double precision,
            'longitude', (sub_loc.location::json#>>'{0,longitude}')::double precision
        ) AS geolocation,
        json_build_object('event_id', events.id) AS entity_ids,
        COALESCE(lp.is_special_event, false) AS is_special_event
    FROM lunar_order_lines AS lol
    JOIN lunar_orders AS lo ON lo.id = lol.order_id
    JOIN users ON users.id = lo.user_id
    JOIN events ON events.order_line_id = lol.id
    JOIN (SELECT max(id) AS id, order_line_id FROM events GROUP BY order_line_id) sub ON sub.id = events.id
    JOIN schedules ON schedules.id = events.schedule_id
    JOIN lunar_product_variants lpv ON lpv.id = lol.purchasable_id
        AND lol.purchasable_type = 'Modules\Ecommerce\Entities\ProductVariant'
    JOIN lunar_products lp ON lp.id = lpv.product_id
        AND lp.status = 'published'
    LEFT JOIN (
        SELECT lpm.product_id, lpm.value AS location
        FROM lunar_products_meta lpm
        WHERE lpm.key = 'locations'
    ) sub_loc ON sub_loc.product_id = lp.id
    LEFT JOIN countries country_booked ON (
        country_booked.alpha2 = UPPER(sub_loc.location::json#>>'{0,country_code}')
        OR country_booked.alpha3 = UPPER(sub_loc.location::json#>>'{0,country_code}')
    )
    WHERE lol.type = 'event'
        AND events.status IN ('upcoming','ongoing')

    UNION ALL

    -- SPACE EVENTS (admin-created venue events)
    SELECT DISTINCT ON (se.id)
        'space_event' AS type,
        se.id,
        NULL AS user_id,
        se.title AS title,
        NULL AS titla_alt,
        m.media_id AS photo_media_id,
        se.started_at AS started_at,
        se.ended_at AS ended_at,
        NULL AS duration,
        NULL AS duration_unit,
        se.timezone AS timezone,
        CASE se.is_same_address_as_parent WHEN true THEN s.address ELSE se.address END AS address,
        CASE se.is_same_address_as_parent WHEN true THEN s.city ELSE se.city END AS city,
        COALESCE(
            country_space.alpha2,
            CASE se.is_same_address_as_parent WHEN true THEN s.country_code ELSE se.country_code END
        ) AS country_code,
        CASE se.is_same_address_as_parent
            WHEN true THEN json_build_object('latitude', s.latitude::double precision, 'longitude', s.longitude::double precision)
            ELSE json_build_object('latitude', se.latitude::double precision, 'longitude', se.longitude::double precision)
        END AS geolocation,
        json_build_object('space_id', s.id) AS entity_ids,
        COALESCE(se_product.is_special_event, false) AS is_special_event
    FROM space_events AS se
    JOIN spaces s ON s.id = se.space_id
    LEFT JOIN mediables m ON m.mediable_id = s.id AND m.mediable_type = 'Modules\Space\Entities\Space' AND m.type = 'logo'
    LEFT JOIN lunar_products se_product ON (
        se_product.productable_id = s.id
        AND se_product.productable_type = 'Modules\Space\Entities\Space'
    )
    LEFT JOIN countries country_space ON (
        country_space.alpha2 = UPPER(
            CASE se.is_same_address_as_parent WHEN true THEN s.country_code ELSE se.country_code END
        )
        OR country_space.alpha3 = UPPER(
            CASE se.is_same_address_as_parent WHEN true THEN s.country_code ELSE se.country_code END
        )
    )
    WHERE se.status = 'published'
);

SELECT type, COUNT(*) AS count
FROM event_calendars
GROUP BY type
ORDER BY type;
