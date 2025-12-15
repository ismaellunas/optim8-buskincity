#!/bin/bash
# Quick script to check Heroku database status

echo "╔════════════════════════════════════════════════════════╗"
echo "║     🔍 HEROKU DATABASE STATUS CHECK                    ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

echo "1️⃣  Checking for PostgreSQL addon..."
echo "───────────────────────────────────────────────────────"
heroku addons | grep -i postgres && echo "✅ PostgreSQL addon found" || echo "❌ No PostgreSQL addon found!"
echo ""

echo "2️⃣  Checking DATABASE_URL configuration..."
echo "───────────────────────────────────────────────────────"
DB_URL=$(heroku config:get DATABASE_URL 2>/dev/null)
if [ -n "$DB_URL" ]; then
    echo "✅ DATABASE_URL is set"
    echo "   Host: $(echo $DB_URL | sed -n 's/.*@\(.*\):.*/\1/p')"
else
    echo "❌ DATABASE_URL is NOT set!"
fi
echo ""

echo "3️⃣  Checking database connection..."
echo "───────────────────────────────────────────────────────"
heroku pg:info 2>/dev/null && echo "✅ Database is accessible" || echo "❌ Cannot access database!"
echo ""

echo "════════════════════════════════════════════════════════"
echo ""
echo "📋 DIAGNOSIS:"
echo ""

if ! heroku addons | grep -q postgres; then
    echo "❌ NO DATABASE PROVISIONED"
    echo ""
    echo "   Fix: Provision a PostgreSQL database"
    echo ""
    echo "   Command:"
    echo "   $ heroku addons:create heroku-postgresql:mini"
    echo "   $ heroku pg:wait"
    echo ""
elif [ -z "$DB_URL" ]; then
    echo "⚠️  DATABASE EXISTS BUT NOT CONFIGURED"
    echo ""
    echo "   Fix: Attach the database to your app"
    echo ""
    echo "   Command:"
    echo "   $ heroku addons:attach \$(heroku addons | grep postgres | awk '{print \$1}')"
    echo ""
else
    echo "✅ DATABASE IS CONFIGURED"
    echo ""
    echo "   If deployment still fails, the database might be:"
    echo "   - Still initializing (wait a few minutes)"
    echo "   - Having connectivity issues (check Heroku status)"
    echo ""
    echo "   Try:"
    echo "   $ heroku pg:wait"
    echo "   $ git push heroku main"
    echo ""
fi

echo "════════════════════════════════════════════════════════"
echo ""
echo "For detailed troubleshooting, see:"
echo "📖 HEROKU_DATABASE_SETUP.md"
echo ""
