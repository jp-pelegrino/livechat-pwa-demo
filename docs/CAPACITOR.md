# Capacitor Integration Guide

This document explains how Capacitor has been integrated into the LiveChat PWA Demo to provide native app capabilities, especially for iOS devices.

## Overview

Capacitor is a cross-platform native runtime that allows web applications to run as native iOS and Android apps. This integration solves several PWA limitations on iOS, including:

- **Better icon handling** - Proper app icons without glitching during installation
- **Native push notifications** - More reliable than web push notifications on iOS
- **Native UI integration** - Better status bar handling and safe area support
- **Offline capabilities** - Enhanced caching and local storage

## Project Structure

```
livechat-pwa-demo/
├── capacitor.config.json     # Capacitor configuration
├── package.json               # Node dependencies and scripts
├── public/                    # Web assets (served by CodeIgniter)
│   ├── capacitor/            # Capacitor runtime files
│   ├── icon-*.png            # App icons (various sizes)
│   ├── manifest.json         # PWA manifest
│   └── sw.js                 # Service worker
├── ios/                      # iOS native project (gitignored)
└── android/                  # Android native project (gitignored)
```

## Icon Configuration

The app now includes properly sized icons for all platforms:

- **icon-120.png** - iPhone home screen (120x120)
- **icon-152.png** - iPad home screen (152x152)
- **icon-167.png** - iPad Pro home screen (167x167)
- **icon-180.png** - iPhone Plus/Pro home screen (180x180)
- **icon-192.png** - Android home screen (192x192)
- **icon-512.png** - High-resolution icon (512x512)

These icons are referenced in:
- `manifest.json` - For PWA installation
- `chat.php` - Via apple-touch-icon meta tags for iOS

## PWA vs Native App

The application works in two modes:

### 1. Progressive Web App (PWA)
- Access via web browser (Safari, Chrome, etc.)
- Add to home screen using browser's native "Add to Home Screen" feature
- Uses service workers for offline functionality
- Uses Web Push API for notifications (limited on iOS)

### 2. Native App (via Capacitor)
- Built as native iOS/Android app
- Distributed via App Store/Play Store
- Better iOS support for push notifications
- Native UI integration
- Auto-detects and uses Capacitor APIs when available

## How It Works

The `chat.php` view includes Capacitor detection:

```javascript
const isCapacitor = window.Capacitor?.isNativePlatform?.();
```

Based on this detection, the app:
- Uses Capacitor PushNotifications API in native mode
- Falls back to Web Notifications API in PWA mode
- Hides PWA install prompts when running natively

## Development Setup

### Prerequisites

For iOS development:
- macOS with Xcode installed
- CocoaPods installed (`sudo gem install cocoapods`)

For Android development:
- Android Studio installed
- Android SDK configured

### Installing Dependencies

```bash
npm install
```

### Syncing Changes

After modifying web assets or Capacitor configuration:

```bash
npm run cap:sync
```

This copies web assets and updates native projects.

### Opening Native Projects

To open in Xcode (iOS):
```bash
npm run cap:open:ios
```

To open in Android Studio:
```bash
npm run cap:open:android
```

## Building for Production

### iOS

1. Sync the project:
   ```bash
   npm run cap:sync:ios
   ```

2. Open in Xcode:
   ```bash
   npm run cap:open:ios
   ```

3. Configure signing in Xcode:
   - Select your development team
   - Configure bundle identifier
   - Set up provisioning profile

4. Build and archive for App Store submission

### Android

1. Sync the project:
   ```bash
   npm run cap:sync:android
   ```

2. Open in Android Studio:
   ```bash
   npm run cap:open:android
   ```

3. Configure signing:
   - Create signing key
   - Update `android/app/build.gradle`

4. Build release APK or AAB

## Configuration

### Server URL for Development

In `capacitor.config.json`, the server URL is set for development:

```json
{
  "server": {
    "url": "http://localhost",
    "cleartext": true
  }
}
```

For production builds:
- Remove the `server.url` configuration
- The app will load from bundled web assets
- Configure your production API endpoint in the app

### Push Notifications

Push notifications are configured to work in both modes:

**PWA Mode:**
- Uses Service Worker and Web Push API
- Requires VAPID keys
- Limited functionality on iOS

**Native Mode (Capacitor):**
- Uses Capacitor PushNotifications plugin
- Better iOS support
- Requires APNs configuration for iOS
- Requires FCM configuration for Android

## Updating Capacitor

To update Capacitor to the latest version:

```bash
npm update @capacitor/core @capacitor/cli @capacitor/ios @capacitor/android
npm run cap:update
```

## Troubleshooting

### iOS Icons Not Appearing

Make sure:
1. Icons are proper PNG files (not 1x1 placeholders)
2. Icons are the correct dimensions
3. Icons are referenced in both `manifest.json` and `chat.php`
4. Clear browser cache and reinstall PWA

### Capacitor Not Detected

1. Check that `/capacitor/capacitor.js` is loading
2. Verify the script tag is before your app code
3. Check browser console for errors

### WebSocket Connection Issues

When running as native app:
1. Ensure `server.url` points to accessible server
2. Check CORS configuration on server
3. For production, use secure WebSocket (wss://)

## Additional Resources

- [Capacitor Documentation](https://capacitorjs.com/docs)
- [iOS Human Interface Guidelines](https://developer.apple.com/design/human-interface-guidelines/ios)
- [Android App Icon Guidelines](https://developer.android.com/google-play/resources/icon-design-specifications)
- [PWA Best Practices](https://web.dev/progressive-web-apps/)

## Testing on iOS

### As PWA:
1. Open app in Safari on iOS device
2. Tap Share button
3. Select "Add to Home Screen"
4. Verify icon appears correctly

### As Native App:
1. Build and install via Xcode
2. Launch from home screen
3. Verify Capacitor detection works
4. Test push notifications
5. Verify WebSocket connectivity

## Migration Notes

This Capacitor integration maintains backward compatibility:
- Existing PWA functionality continues to work
- Web app works without changes
- Capacitor features are opt-in enhancements
- No breaking changes to existing API
