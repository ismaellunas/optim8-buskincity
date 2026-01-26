-- FIX: Recreate the event_calendars view
-- The view is outdated and not showing booked_event entries

-- Drop and recreate the view
DROP VIEW IF EXISTS event_calendars;

-- Recreate with the correct query
CREATE VIEW event_calendars AS (
    -- BOOKED EVENTS (from bookings)
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
    LEFT JOIN (SELECT lpm.product_id, lpm.value AS location from lunar_products_meta lpm WHERE lpm.key = 'locations') sub_loc ON sub_loc.product_id = lp.id
    WHERE lol.type = 'event'
        AND events.status IN ('upcoming','ongoing')

    UNION ALL

    -- SPACE EVENTS
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
    LEFT JOIN mediables m ON m.mediable_id = s.id AND m.mediable_type = 'Modules\Space\Entities\Space' AND m.type = 'logo'
    WHERE se.status = 'published'

    UNION ALL

    -- PRODUCT EVENTS (pre-scheduled events)
    SELECT DISTINCT ON (pe.id)
        'product_event' AS type,
        pe.id,
        NULL AS user_id,
        pe.title AS title,
        NULL AS title_alt,
        NULL AS photo_media_id,
        pe.started_at AS started_at,
        pe.ended_at AS ended_at,
        NULL AS duration,
        NULL AS duration_unit,
        pe.timezone AS timezone,
        pe.address AS address,
        pe.city AS city,
        pe.country_code AS country_code,
        json_build_object('latitude', pe.latitude::double precision, 'longitude', pe.longitude::double precision) AS geolocation,
        json_build_object('product_id', lp.id, 'product_event_id', pe.id) AS entity_ids
    FROM product_events AS pe
    JOIN lunar_products lp ON lp.id = pe.product_id
        AND lp.status = 'published'
    WHERE pe.status = 'published'
);

-- Verify the fix worked
SELECT 
    'After Fix' as status,
    type,
    COUNT(*) as count
FROM event_calendars
GROUP BY type
ORDER BY type;

-- Check if your Örebro event is now visible
SELECT 
    type,
    title,
    city,
    country_code,
    started_at
FROM event_calendars
WHERE city ILIKE '%rebro%'
   OR started_at::date = '2026-02-09';
