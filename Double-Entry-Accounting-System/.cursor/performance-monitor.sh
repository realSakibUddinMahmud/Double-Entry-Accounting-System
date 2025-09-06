#!/bin/bash

# Cursor Performance Monitor
# Run this script to monitor Cursor's resource usage

echo "🚀 Cursor Performance Monitor"
echo "=============================="

# Check Cursor process
echo "📊 Cursor Process Status:"
if pgrep -f "Cursor" > /dev/null; then
    echo "✅ Cursor is running"
    
    # Get Cursor PID
    CURSOR_PID=$(pgrep -f "Cursor" | head -1)
    echo "🆔 Process ID: $CURSOR_PID"
    
    # Memory usage
    echo "💾 Memory Usage:"
    ps -o pid,ppid,cmd,%mem,%cpu --no-headers -p $CURSOR_PID
    
    # CPU usage
    echo "⚡ CPU Usage:"
    top -p $CURSOR_PID -n 1 -b | tail -1
    
    # File descriptors
    echo "📁 Open Files:"
    lsof -p $CURSOR_PID | wc -l | xargs echo "   Count:"
    
else
    echo "❌ Cursor is not running"
fi

echo ""
echo "🔍 System Resources:"
echo "===================="

# System memory
echo "💻 System Memory:"
free -h

# Disk usage
echo ""
echo "💿 Disk Usage:"
df -h | grep -E 'Filesystem|/dev/'

# CPU load
echo ""
echo "⚡ CPU Load:"
uptime

# Active processes
echo ""
echo "🔄 Active Processes:"
ps aux | grep -i cursor | grep -v grep

echo ""
echo "📈 Performance Tips:"
echo "===================="
echo "1. Close unused tabs and files"
echo "2. Restart Cursor if memory usage is high"
echo "3. Check .cursorignore and .cursor/settings.json"
echo "4. Monitor large files in your workspace"
echo "5. Use workspace-specific settings"

echo ""
echo "🎯 Current Workspace: $(pwd)"
echo "📁 Files in workspace: $(find . -type f | wc -l)"
echo "📊 Workspace size: $(du -sh . | cut -f1)"
