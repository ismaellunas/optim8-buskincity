# Google Maps Zoom Levels Reference

## What Changed

**EventsCalendar zoom:** Changed from **4** (region) → **11** (city/municipal)

---

## Zoom Level Guide

| Zoom Level | View | Use Case | Example |
|------------|------|----------|---------|
| 1 | World | Global view | Entire planet |
| 2 | World | Default world view | Multiple continents |
| 3 | Continent | Fallback minimum | Asia, Europe |
| **4** | **Country/Region** | **OLD setting** | **Entire Philippines** |
| 5 | Country | Large country view | Philippines + neighbors |
| 6-7 | Large region | Multiple provinces | Visayas region |
| 8-9 | Province | Province/state level | Cebu province |
| 10 | Multiple cities | City cluster | Metro Manila |
| **11** | **City/Municipal** | **NEW setting ✅** | **Your municipality** |
| 12 | City | City level | Quezon City |
| 13 | District | District/neighborhood | Makati CBD |
| 14 | Neighborhood | Local area | Barangay level |
| 15 | Street | Street level | Block view |
| 16-17 | Building | Very close | Individual buildings |
| 18+ | Building detail | Maximum zoom | Building details |

---

## Your Current Settings

### EventsCalendar (Events page):
- **World view**: Zoom 2 (when no location)
- **Your location**: Zoom 11 ✅ (city/municipal level)
- **Max zoom**: 17 (can zoom closer if needed)
- **Min zoom**: 3 (can't zoom out past continent)

### GmapMarker (Location picker):
- **Your location**: Zoom 11 (city level)
- **World view**: Zoom 2 (when no location)

---

## If You Want Different Zoom Levels

### Want to see more area (zoom out):
```javascript
// Show more of the region/province
zoom: 9  // Province level
zoom: 8  // Large region
```

### Want to see exact location (zoom in):
```javascript
// Show neighborhood/street level
zoom: 13  // District
zoom: 14  // Neighborhood  
zoom: 15  // Street level
```

### Current (balanced):
```javascript
zoom: 11  // City/municipal - good balance ✅
```

---

## Testing

After rebuilding (`npm run dev`), the map should:

1. ✅ Center on your location (Philippines)
2. ✅ Zoom to level 11 (you'll see your city/municipality)
3. ✅ Show nearby streets and landmarks
4. ✅ Stay there (no jumping)

---

## Adjust If Needed

If zoom level 11 is:
- **Too close** (can't see enough area) → Use zoom 9 or 10
- **Too far** (want to see your exact street) → Use zoom 13 or 14
- **Just right** → Keep 11 ✅

To change, edit these lines in `EventsCalendar.vue`:
- Line ~366: `zoom: (lat === 0 && lng === 0) ? 2 : 11,`
- Line ~384: `this.map.setZoom(11);`

---

**Ready to test!** Run `npm run dev` and hard refresh your browser.
