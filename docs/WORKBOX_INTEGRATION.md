# Workbox Integration for Enhanced PWA Support

## Overview

This document explains how Workbox has been integrated into the LiveChat PWA to provide robust background sync, push notifications, and offline support - especially when the app is backgrounded on iOS and Android.

## Why Workbox?

As recommended by the [Capacitor PWA documentation](https://capacitorjs.com/docs/web/progressive-web-apps), Workbox provides production-ready Service Worker recipes that are more reliable and maintainable than writing custom Service Workers from scratch.

### Benefits

1. **Background Sync**: Automatically retries failed network requests when the connection is restored
2. **Push Notifications**: Delivers notifications even when the app is closed or backgrounded
3. **Offline Support**: Caches assets intelligently for offline access
4. **Automatic Retries**: Failed message sends are queued and retried automatically
5. **Better iOS Support**: Enhanced reliability for PWA features on iOS devices

## Implementation

### Service Worker (sw.js)

The service worker now uses Workbox for:

1. **Precaching**: Static assets (icons, manifest) are cached during installation
2. **Runtime Caching**: Dynamic caching strategies for different resource types
3. **Background Sync**: Failed POST requests are queued and retried when online
4. **Push Notifications**: Enhanced push notification handling that works when backgrounded

### Caching Strategies

#### Images
- **Strategy**: CacheFirst
- **Cache Name**: `livechat-images`
- **Max Age**: 30 days
- **Max Entries**: 60 images

#### CSS and JavaScript
- **Strategy**: StaleWhileRevalidate
- **Cache Name**: `livechat-assets`
- Always serves cached version while updating in background

#### HTML Pages
- **Strategy**: NetworkFirst
- **Cache Name**: `livechat-pages`
- Tries network first, falls back to cache if offline

#### API Requests (POST)
- **Strategy**: NetworkOnly with BackgroundSyncPlugin
- **Queue Name**: `message-queue`
- **Retry Duration**: 24 hours
- Automatically retries failed message sends

## Background Sync

### Message Queue

When a message fails to send (e.g., user is offline or connection drops):

1. Message is added to the `message-queue` in IndexedDB
2. When connection is restored, Workbox automatically retries the request
3. If successful, the message is removed from the queue
4. If it fails again, it stays in the queue for up to 24 hours

### Sync Events

The service worker listens for two types of sync events:

1. **`sync-messages`**: Triggered when the app comes back online
2. **`check-messages`**: Periodic sync to check for new messages (Chrome only)

## Push Notifications

### Enhanced Push Handler

The service worker's push event handler:

1. **Works when backgrounded**: Notifications are delivered even if the app is closed
2. **Rich notifications**: Includes username, room info, and action buttons
3. **Client communication**: Notifies open app instances of new messages
4. **Smart focusing**: Clicking a notification focuses existing app window or opens new one

### Notification Actions

- **Open Chat**: Opens the app and switches to the relevant room
- **Dismiss**: Closes the notification

### Example Push Payload

```json
{
  "title": "John Doe",
  "body": "Hey, how are you?",
  "username": "John Doe",
  "roomId": "1",
  "messageId": "123",
  "url": "/"
}
```

## Client-Side Integration

### Service Worker Messages

The app listens for messages from the service worker:

- **`sync-messages`**: Triggers local message queue processing
- **`check-messages`**: Refreshes messages from server
- **`push-received`**: Handles push notifications received while app is open
- **`switch-room`**: Switches to the room when user clicks notification

### Registration

```javascript
// Service Worker registered with update check
const reg = await navigator.serviceWorker.register('/sw.js', {
    updateViaCache: 'none'
});

// Listen for updates
reg.addEventListener('updatefound', () => {
    // Handle new version available
});
```

## Background Persistence Features

### 1. Enhanced Keepalive (15s intervals)

Maintains WebSocket connection with frequent pings.

### 2. Message Queueing

Messages sent while offline are:
- Stored in localStorage (for app-level persistence)
- Stored in IndexedDB via Workbox (for service worker-level persistence)
- Automatically synced when connection is restored

### 3. Visibility Detection

Detects when app is:
- Backgrounded (hidden)
- Foregrounded (visible)
- Suspended (page hide)
- Restored (page show)

### 4. Network Status

Monitors online/offline status and triggers reconnection when back online.

## Testing

### Test Background Sync

1. Open the app and send a message
2. Turn off network connection
3. Send another message (it will be queued)
4. Turn network back on
5. Message should be sent automatically

### Test Push Notifications

1. Enable notifications in the app
2. Send a push notification from server
3. App should show notification even when:
   - App is backgrounded
   - App is closed
   - Device is locked (iOS limitations apply)

### Test Offline Mode

1. Open the app while online
2. Turn off network connection
3. Navigate the app (cached pages should load)
4. Try to send messages (they should queue)
5. Turn network back on
6. Queued messages should send automatically

## iOS Considerations

### Push Notifications

- iOS requires HTTPS for push notifications
- Safari requires user interaction before showing notification permission prompt
- Push notifications on iOS work best when app is added to home screen
- Background sync has limitations on iOS (works better when app is open)

### Background Sync

- iOS Safari has strict background execution limits
- Background sync works when app returns to foreground
- Use Capacitor native app for better background capabilities on iOS

## Android Considerations

### Push Notifications

- Works reliably in background and when app is closed
- Supports rich notifications with actions
- Background sync works well

### Background Sync

- More reliable than iOS
- Periodic background sync works (Chrome only)
- Better battery management

## Monitoring and Debugging

### Service Worker DevTools

1. Open Chrome DevTools
2. Go to Application > Service Workers
3. View registered service workers and their status
4. Use "Update" to force service worker update
5. Use "Unregister" to remove service worker

### Background Sync

1. In DevTools: Application > Background Sync
2. View queued sync events
3. Manually trigger sync events for testing

### Cache Storage

1. In DevTools: Application > Cache Storage
2. View all cached assets
3. Clear caches for testing

### IndexedDB

1. In DevTools: Application > IndexedDB
2. View `workbox-background-sync` database
3. See queued requests in the `message-queue` object store

## Workbox Configuration

### Version

Using Workbox 7.0.0 from CDN:
```javascript
importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js');
```

### Plugins Used

- **CacheableResponsePlugin**: Caches only successful responses
- **ExpirationPlugin**: Manages cache expiration and size limits
- **BackgroundSyncPlugin**: Handles background sync for failed requests

### Strategies Used

- **CacheFirst**: For images (fast loading)
- **NetworkFirst**: For HTML pages (fresh content)
- **StaleWhileRevalidate**: For CSS/JS (balance of speed and freshness)
- **NetworkOnly**: For API requests (with background sync fallback)

## Resources

- [Workbox Documentation](https://developers.google.com/web/tools/workbox/)
- [Service Worker API (MDN)](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Using Service Workers (MDN)](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API/Using_Service_Workers)
- [Capacitor PWA Docs](https://capacitorjs.com/docs/web/progressive-web-apps)
- [Background Sync API](https://developer.mozilla.org/en-US/docs/Web/API/Background_Synchronization_API)

## Troubleshooting

### Service Worker Not Updating

1. Clear browser cache
2. Unregister service worker in DevTools
3. Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)
4. Check `updateViaCache: 'none'` in registration

### Background Sync Not Working

1. Check if browser supports Background Sync API
2. Verify network connection
3. Check DevTools > Application > Background Sync
4. Ensure service worker is active

### Push Notifications Not Received

1. Check notification permissions
2. Verify HTTPS connection
3. Test with browser notifications first
4. Check service worker console for errors
5. Verify push subscription is active

## Future Enhancements

1. **Workbox Webpack Plugin**: For automated asset precaching
2. **Workbox CLI**: For generating service worker config
3. **Advanced Routing**: Custom routing rules for specific API endpoints
4. **Cache Warming**: Preload frequently accessed resources
5. **Analytics**: Track service worker performance and usage
