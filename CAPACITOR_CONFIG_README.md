# Capacitor Configuration Notes

## Development Configuration (capacitor.config.json)

The current `capacitor.config.json` is configured for **local development only**.

### Important Security Notes

⚠️ **WARNING**: The development configuration uses HTTP (`http://localhost`) with `cleartext: true`. This is intended for local development only and has security implications:

- **HTTP is insecure** - Data is transmitted in plaintext
- **Push notifications** may not work properly on iOS (requires HTTPS)
- **Some browser APIs** require secure context (HTTPS)
- **Never use this in production** - Always use HTTPS for production

### Development Options

For better development experience, especially when testing on physical devices:

1. **Use a tunneling service** (recommended for iOS testing):
   ```bash
   # Install ngrok or similar
   ngrok http 80
   
   # Update capacitor.config.json with the HTTPS URL
   {
     "server": {
       "url": "https://your-ngrok-url.ngrok.io"
     }
   }
   ```

2. **Set up local HTTPS** with self-signed certificates:
   - Configure nginx or Apache with SSL
   - Update the URL to `https://localhost`
   - Remove `cleartext: true`

3. **Continue with HTTP** for basic testing (current setup):
   - Only works reliably on localhost
   - Some features may not work on physical devices
   - Not suitable for testing secure features

## Production Configuration (capacitor.config.production.json)

The `capacitor.config.production.json` file removes the server configuration, making the app load from bundled assets. Use this for App Store/Play Store builds:

```bash
# Switch to production config
cp capacitor.config.production.json capacitor.config.json

# Sync to native projects
npm run cap:sync

# Build for production
npm run cap:open:ios  # or cap:open:android
```

## Version Pinning

The package.json uses caret (^) ranges for Capacitor packages (e.g., `^6.0.0`). This allows minor and patch updates automatically.

**For production builds**, consider using exact versions for reproducibility:

```json
{
  "dependencies": {
    "@capacitor/core": "6.2.1",
    "@capacitor/cli": "6.2.1",
    "@capacitor/ios": "6.2.1",
    "@capacitor/android": "6.2.1"
  }
}
```

## More Information

See [docs/CAPACITOR.md](docs/CAPACITOR.md) for complete documentation on Capacitor integration, build process, and troubleshooting.
