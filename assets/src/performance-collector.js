const sendMetricToBackend = (metric) => {
  console.log('Sending metric:', metric);

  // Ensure postId is available
  const postId = window.wpSeoCheckPerformance && window.wpSeoCheckPerformance.postId;
  if (!postId) {
    console.error('Post ID not available for performance metric.');
    return;
  }

  fetch('/wp-json/wp-seo-check/v1/performance-metrics', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      post_id: postId,
      name: metric.name,
      value: metric.value,
      id: metric.id // Keep original ID for debugging/context
    }),
  })
  .then(response => response.json())
  .then(data => console.log('Metric sent successfully:', data))
  .catch(error => console.error('Error sending metric:', error));
};

// Basic implementation for CLS, LCP, FID, TTFB using PerformanceObserver
// This is a simplified version for demonstration and might not be as robust as web-vitals library

const getMetric = (name, entryType, callback) => {
  if (PerformanceObserver.supportedEntryTypes.includes(entryType)) {
    const observer = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        callback({ name, value: entry.duration || entry.value, id: entry.name });
      }
    });
    observer.observe({ type: entryType, buffered: true });
  }
};

// CLS (Cumulative Layout Shift)
getMetric('CLS', 'layout-shift', (metric) => {
  sendMetricToBackend(metric);
});

// LCP (Largest Contentful Paint)
getMetric('LCP', 'largest-contentful-paint', (metric) => {
  sendMetricToBackend(metric);
});

// FID (First Input Delay) - requires user interaction, so it's harder to capture directly without a real user
// For simplicity, we'll just log a placeholder for now.
// A more robust solution would involve a polyfill or a dedicated library.
// getMetric('FID', 'first-input', (metric) => {
//   sendMetricToBackend(metric);
// });

// TTFB (Time to First Byte)
getMetric('TTFB', 'navigation', (metric) => {
  // For TTFB, we need to calculate it from navigation timing
  const navEntry = performance.getEntriesByType('navigation')[0];
  if (navEntry) {
    sendMetricToBackend({ name: 'TTFB', value: navEntry.responseStart - navEntry.requestStart, id: navEntry.name });
  }
});

// Fallback for FID (simplified)
window.addEventListener('load', () => {
  if (typeof PerformanceObserver === 'undefined' || !PerformanceObserver.supportedEntryTypes.includes('first-input')) {
    // Fallback for browsers not supporting First Input
    sendMetricToBackend({ name: 'FID', value: 'N/A (Polyfill needed)', id: 'fallback-fid' });
  }
});