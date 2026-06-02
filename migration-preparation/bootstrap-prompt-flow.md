# Joxig.se: AI Bootstrapping & Development Prompt Flow

> **Purpose:** A sequential prompt sequence to feed into your AI coding assistant. Follow these prompts in order to build, iterate, and deploy Joxig.se using the unified Next.js stack.

---

## Prompt 1: The Master Bootstrap
*Use this prompt to initialize the project, lay down the base architecture, and get agreement from the AI on the folder structure and schemas.*

```markdown
I want you to act as a Senior Software Architect and bootstrap a "Unified Fullstack" MVP for "Joxig.se", a digital billboard platform for craftsmen.

### Tech Stack
- Framework: Next.js (App Router, using API Routes for the backend)
- Database: PostgreSQL (via Prisma ORM)
- Headless CMS: Sanity.io (for marketing content and blog)
- Styling: Tailwind CSS
- Validation: Zod
- Client Data Fetching: TanStack Query
- Testing: Vitest

### Architecture Strategy: "The Service Layer"
To ensure we can easily migrate to NestJS in the future, you MUST separate business logic from the Next.js route handlers.
- Infrastructure: Create a `services/` directory at the root.
- Brain: All database queries, geolocation calculations, and business logic must live in standard TypeScript classes/functions within `services/`.
- API Routes: Next.js API routes should ONLY handle the request/response and call these services.

### Core App Purpose (The "Billboard" Model)
The application connects site visitors with local craftsmen (e.g., carpenters).
- Visitor Flow: Browse a directory and use a Map view to find the closest craftsmen via geolocation.
- Craftsman Flow: Presentation pages listing skills, bio, and DIRECT contact details (phone/email).
- Admin Flow: Approve/manage craftsmen and view basic stats.
- CRITICAL: There are NO payments, NO bookings, and NO internal messaging.

### MVP Features
- Craftsman Directory & Categories (e.g., Carpenters, Plumbers).
- Geolocation Discovery: Logic to calculate and sort craftsmen by distance to the visitor.
- Supplier Onboarding: A hardcoded, strictly typed Zod-validated "Become a Supplier" form.

### Deployment & Structure
- Target: Single Heroku Dyno (One process, one port).
- Folders: `/app`, `/services`, `/components`, `/prisma`, `/studio`.

### Action Required:
1. Propose the exact file structure.
2. Outline the Prisma schema for `User`, `CraftsmanProfile`, and `Category`.
3. Explain the Strategy for map-based distances (e.g., PostGIS vs Haversine formula).
Wait for my approval before writing generation scripts.
```

---

## Prompt 2: Database & Authentication Setup
*Once the AI outputs the structure and you approve it, run this prompt to build the foundation.*

```markdown
I approve the plan. Please proceed with Phase 1: Foundation & Authentication.

Action Required:
1. Generate the initial `package.json` with necessary dependencies.
2. Provide the exact commands to initialize Next.js, Prisma, and Tailwind.
3. Write the final `schema.prisma` file based on our earlier discussion.
4. Set up Authentication (Session or JWT) with Role-based access control (Admin, Craftsman, Visitor).
5. Generate the backend Auth Service (`services/auth.service.ts`) and the corresponding Next.js API route.
```

---

## Prompt 3: Craftsman Discovery & Geolocation
*This tackles the core logic of the platform.*

```markdown
Great. Let's move to Phase 2: Craftsman Discovery & Geolocation.

Action Required:
1. Write the `services/craftsman.service.ts`. It must include a function `findClosestCraftsmen(lat, lng, radius)` that calculates distance using our Prisma schema.
2. Write the Next.js API route (`app/api/craftsmen/route.ts`) to expose this.
3. Create the frontend TanStack Query hook `useCraftsmen(lat, lng)`.
4. Scaffold two UI components using Tailwind:
   - `CraftsmanCard.tsx` (shows name, category, and distance).
   - `MapDirectoryView.tsx` (a layout to display the closest results). 
Remember: Keep styling purely to Tailwind utility classes.
```

---

## Prompt 4: Supplier Onboarding Form
*Building the hardcoded application form to bypass the old dynamic builder.*

```markdown
Now we build the "Become a Supplier" onboarding flow.

Action Required:
1. Define a Zod schema (`schemas/onboarding.schema.ts`) for a Craftsman application (Name, Email, Phone, Category, Lat/Lng).
2. Create the frontend React form component using React Hook Form combined with Zod resolver for validation.
3. Build the `services/application.service.ts` to save this data to an "Applications" table in Prisma.
4. Create the API route `/api/apply` that accepts the POST request.
```

---

## Prompt 5: Sanity CMS Integration
*Setting up content management.*

```markdown
Let's integrate Sanity CMS so admins can edit landing pages without modifying code.

Action Required:
1. Provide the setup instructions for `sanity init` within our `/studio` folder.
2. Generate a Sanity schema for a "Landing Page" (Title, Slug, Hero Image, Content Blocks).
3. Write a utility function in Next.js (`lib/sanity.client.ts`) to fetch this data.
4. Create a dynamic Next.js route `app/(marketing)/[slug]/page.tsx` that fetches and renders a Sanity page.
```

---

## Prompt 6: Deployment & Heroku Configuration
*The final step to prep the app for production.*

```markdown
The MVP is ready. Prepare the project for deployment to a single Heroku Dyno.

Action Required:
1. Draft the `Procfile` required by Heroku to boot the Next.js server.
2. Write the required Heroku Buildpack configuration or instructions if we need to support Prisma binaries.
3. Provide a script for the `package.json` that runs Prisma migrations automatically during the Heroku build phase (`vercel-build` or `heroku-postbuild`).
4. List all Environment Variables (Config Vars) I need to set in the Heroku dashboard (e.g., DATABASE_URL, SANITY_PROJECT_ID, NEXTAUTH_SECRET).
```
