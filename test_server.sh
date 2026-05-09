#!/bin/bash
cd /home/evan/dev/05/nativa

# Start PHP server
php -S localhost:8952 -t public > /tmp/server46.log 2>&1 &
SERVER_PID=$!

sleep 2

# Test endpoints
echo -n "GET /articles: "
curl -s -o /dev/null -w "%{http_code}" http://localhost:8952/articles
echo ""

echo -n "GET /portfolio: "
curl -s -o /dev/null -w "%{http_code}" http://localhost:8952/portfolio
echo ""

echo -n "GET /: "
curl -s -o /dev/null -w "%{http_code}" http://localhost:8952/
echo ""

# Stop server
kill $SERVER_PID 2>/dev/null
sleep 1

# Show server log
echo ""
echo "=== Server Log ==="
cat /tmp/server46.log | grep -E "Accepted|200|404|500" | head -15
