// ===== DEBUGGING INFINITE REFRESH LOOP =====
// Add this to console to identify the root cause

// Override location.reload to trace all calls
const originalReload = window.location.reload.bind(window.location);
window.location.reload = function(forceReload) {
    console.error('🔴 RELOAD TRIGGERED!');
    console.trace('Call stack trace:');
    console.error('Stack:', new Error().stack);
    
    // Still allow the reload to happen
    return originalReload(forceReload);
};

// Log all setInterval calls
const originalSetInterval = window.setInterval;
window.setInterval = function(callback, delay, ...args) {
    const stack = new Error().stack;
    console.log(`⏰ setInterval registered with delay ${delay}ms`);
    console.log('Stack:', stack);
    
    // Wrap the callback to log when it executes
    const wrappedCallback = function() {
        console.log(`⏳ setInterval executing (${delay}ms interval)`);
        try {
            return callback.apply(this, arguments);
        } catch (e) {
            console.error('Error in interval callback:', e);
            throw e;
        }
    };
    
    return originalSetInterval(wrappedCallback, delay, ...args);
};

console.log('✅ Debug instrumentation installed. Watch console for reload triggers.');
