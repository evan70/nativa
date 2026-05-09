#!/bin/bash
cd /home/evan/dev/05/nativa

# Kill all existing PHP servers
pkill -f "php -S" 2>/dev/null
sleep 1

# Start fresh server
php -S localhost:9000 -t public > /tmp/final_server.log 2>&1 &
SERVER_PID=$!
echo "Server started, PID: $SERVER_PID"

sleep 2

# Test endpoints
echo ""
echo "Testing endpoints:"
echo -n "GET /articles: "
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:9000/articles

echo -n "GET /portfolio: "
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:9000/portfolio

echo -n "GET /: "
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:9000/

# Show server log
echo ""
echo ""
echo "=== Server Log (last 20 lines) ==="
tail -20 /tmp/final_server.log

# Stop server
kill $SERVER_PID 2>/dev/null
echo "Server stopped"
