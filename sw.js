// VES School ERP - Service Worker
// Version 1.0.0

const CACHE_NAME = 'ves-erp-v1.0.0';
const OFFLINE_URL = '/erp/offline.html';

// Files to cache for offline functionality
const CACHE_FILES = [
  '/erp/',
  '/erp/index.php',
  '/erp/index.php',
  '/erp/manifest.json',
  
  // CSS Files
  '/erp/admin/dashboard/css/style.css',
  '/erp/teachers/dashboard/css/style.css',
  '/erp/student/dashboard/css/style.css',
  '/erp/ves-reception/assets/css/style.css',
  
  // JavaScript Files
  '/erp/admin/dashboard/js/main.js',
  '/erp/teachers/dashboard/js/main.js',
  '/erp/student/dashboard/js/main.js',
  '/erp/ves-reception/assets/js/main.js',
  
  // Images
  '/erp/assets/images/school-logo.png',
  '/erp/assets/images/icon-192x192.png',
  '/erp/assets/images/icon-512x512.png',
  
  // Offline page
  '/erp/offline.html'
];

// Install event - cache files
self.addEventListener('install', (event) => {
  console.log('VES ERP Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('VES ERP Service Worker: Caching files');
        return cache.addAll(CACHE_FILES);
      })
      .then(() => {
        console.log('VES ERP Service Worker: All files cached');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('VES ERP Service Worker: Cache failed', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('VES ERP Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME) {
              console.log('VES ERP Service Worker: Deleting old cache', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('VES ERP Service Worker: Activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve cached files or fetch from network
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip requests to external domains
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Handle navigation requests
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          return caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  // Handle other requests with cache-first strategy
  event.respondWith(
    caches.match(event.request)
      .then((cachedResponse) => {
        if (cachedResponse) {
          // Return cached version
          return cachedResponse;
        }

        // Fetch from network
        return fetch(event.request)
          .then((response) => {
            // Don't cache non-successful responses
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response
            const responseToCache = response.clone();

            // Cache the response for future use
            caches.open(CACHE_NAME)
              .then((cache) => {
                cache.put(event.request, responseToCache);
              });

            return response;
          })
          .catch(() => {
            // Return offline page for navigation requests
            if (event.request.destination === 'document') {
              return caches.match(OFFLINE_URL);
            }
          });
      })
  );
});

// Background sync for offline data submission
self.addEventListener('sync', (event) => {
  console.log('VES ERP Service Worker: Background sync triggered', event.tag);
  
  if (event.tag === 'attendance-sync') {
    event.waitUntil(syncAttendanceData());
  }
  
  if (event.tag === 'homework-sync') {
    event.waitUntil(syncHomeworkData());
  }
});

// Push notification handler
self.addEventListener('push', (event) => {
  console.log('VES ERP Service Worker: Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'New notification from VES ERP',
    icon: '/erp/assets/images/icon-192x192.png',
    badge: '/erp/assets/images/icon-72x72.png',
    vibrate: [200, 100, 200],
    data: {
      url: '/erp/'
    },
    actions: [
      {
        action: 'open',
        title: 'Open VES ERP',
        icon: '/erp/assets/images/icon-96x96.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/erp/assets/images/icon-96x96.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('VES School ERP', options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
  console.log('VES ERP Service Worker: Notification clicked');
  
  event.notification.close();

  if (event.action === 'open') {
    event.waitUntil(
      clients.openWindow(event.notification.data.url || '/erp/')
    );
  }
});

// Helper function to sync attendance data
async function syncAttendanceData() {
  try {
    // Get offline attendance data from IndexedDB
    const offlineData = await getOfflineAttendanceData();
    
    if (offlineData.length > 0) {
      // Send data to server
      const response = await fetch('/erp/api/sync-attendance.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(offlineData)
      });

      if (response.ok) {
        // Clear offline data after successful sync
        await clearOfflineAttendanceData();
        console.log('VES ERP Service Worker: Attendance data synced successfully');
      }
    }
  } catch (error) {
    console.error('VES ERP Service Worker: Attendance sync failed', error);
  }
}

// Helper function to sync homework data
async function syncHomeworkData() {
  try {
    // Get offline homework data from IndexedDB
    const offlineData = await getOfflineHomeworkData();
    
    if (offlineData.length > 0) {
      // Send data to server
      const response = await fetch('/erp/api/sync-homework.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(offlineData)
      });

      if (response.ok) {
        // Clear offline data after successful sync
        await clearOfflineHomeworkData();
        console.log('VES ERP Service Worker: Homework data synced successfully');
      }
    }
  } catch (error) {
    console.error('VES ERP Service Worker: Homework sync failed', error);
  }
}

// Placeholder functions for IndexedDB operations
async function getOfflineAttendanceData() {
  // Implementation would use IndexedDB to retrieve offline attendance data
  return [];
}

async function clearOfflineAttendanceData() {
  // Implementation would clear offline attendance data from IndexedDB
}

async function getOfflineHomeworkData() {
  // Implementation would use IndexedDB to retrieve offline homework data
  return [];
}

async function clearOfflineHomeworkData() {
  // Implementation would clear offline homework data from IndexedDB
} 