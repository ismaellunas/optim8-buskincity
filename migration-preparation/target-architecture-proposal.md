# Target Architecture Proposal: Joxig.se Modernization

> **Context:** Transitioning the codebase from the heavily modular Laravel ecosystem (originally BuskinCity) to a **Unified Next.js Fullstack** architecture for **Joxig.se**, a billboard platform for craftsmen.

## 1. Summary of What the App Actually Needs to Do
Joxig.se acts as a digital billboard connecting site visitors with local craftsmen (e.g., carpenters). 
It must execute the following:
1. **Craftsman Enablement:** Allow craftsmen (suppliers) to publish a presentation page listing their location, skills, and direct contact details.
2. **Visitor Discovery & Geolocation:** Allow users to browse a directory and use map-based geolocation to find the closest craftsmen.
3. **Content Management:** Manage marketing content (landing pages, blog) via Sanity CMS.

*(Note: Platform transactions and bookings happen entirely outside the platform.)*

## 2. Essential Business Domains and Workflows
* **Identity & Profiles:** Craftsmen accounts, generic User accounts, and Admin roles.
* **Discovery & Taxonomies:** Categories (e.g., Carpenters) and map-based geological distance search.
* **Content:** Sanity CMS integration for flexible marketing pages.
* **Operations:** Hardcoded, strictly typed forms (e.g., "Apply as Supplier") sending submissions to API routes.

## 3. Recommended Lightweight Architecture (Unified Stack)
To optimize for a **single Heroku Dyno** and maximize development speed, we are flattening the stack:

* **Framework:** **Next.js (App Router)**. This handles both the frontend and the backend (API Routes) in a single unified process.
* **Architecture Pattern:** **Service Layer Separation**. All business and database logic will be held in a `services/` directory, decoupled from the Next.js route handlers. This ensures a clean path to migrate to NestJS in the future if needed.
* **Database:** PostgreSQL via Prisma ORM.
* **Styling:** **Tailwind CSS**.
* **CMS:** **Sanity.io**.

## 4. Suggested Project Structure
A flattened structure using a standard Next.js layout optimized for one-process deployment.

```text
/joxig-app
  /app         (Next.js App Router: Pages & API Routes)
  /services    (The "Brain": Reusable logic for future framework migration)
  /components  (React UI components styled with Tailwind)
  /prisma      (Schema and DB migrations)
  /studio      (Sanity Studio code for Content Management)
  /public      (Static assets)
```

## 5. Tradeoffs and Risks
| Tradeoff / Risk | Mitigation Strategy |
| --- | --- |
| **Next.js API Limits:** Next.js APIs are designed to be stateless/serverless. | We will keep logic stateless and use external workers (or scheduled CRONs) only if complex background tasks arise. |
| **Single Dyno Resources:** Handling web traffic and API traffic in one process. | The billboard model is low-intensity; caching via TanStack Query and Sanity CDN will keep the load minimal. |

## 6. Phased Implementation Plan

### Phase 1: Foundation (Next.js + Tailwind + Sanity)
- Scaffold the Next.js project and Tailwind CSS.
- Set up Prisma with the PostgreSQL schema.
- Connect to Sanity.io and define "Craftsman" and "Category" schemas.

### Phase 2: Identity & Craftsman Discovery
- Implement Auth (JWT or Session) and the Service Layer for Craftsman profiles.
- Build the Geolocation map view (Discover the closest craftsmen).
- Implement the hardcoded "Become a Supplier" registration form.

### Phase 3: Content & Polish
- Connect Sanity content to the Next.js landing pages.
- Configure transactional e-mails for `joxig.se`.
- Port layout elements from BuskinCity, swapping performer branding for craftsmen.
