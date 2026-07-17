# Saint Globle Verify — Mobile App (Capacitor)

This is the **Capacitor Android/iOS shell** for the Saint Globle verification & rewards
platform. It loads the live Laravel app (`server.url` in `capacitor.config.json`) and wraps
it in a native app with splash screen, status bar and native geolocation.

> The web app already ships a **camera QR scanner** (via the browser `BarcodeDetector` API)
> and is an installable **PWA**, so most phones can use it with no app store at all. This
> Capacitor project is for producing a distributable **`.apk` / Play Store build**.

---

## 1. Prerequisites (to compile an APK)

Building an `.apk` needs the Android toolchain — **not installed on the dev machine yet**:

- **JDK 17** (bundled with Android Studio)
- **Android Studio** (includes the Android SDK, platform-tools and Gradle)
  → https://developer.android.com/studio
- Node.js (already installed)

## 2. Point the app at your server

Edit [`capacitor.config.json`](capacitor.config.json) → `server.url`:

- **Real phone on the same Wi‑Fi:** `http://192.168.1.156:8000` (this PC's LAN IP — already set)
- **Android emulator:** `http://10.0.2.2:8000`
- **Production:** `https://your-domain.com` (and remove `cleartext`)

Then make Laravel reachable from the phone (bind to all interfaces, not just localhost):

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

…and allow port 8000 through the Windows Firewall.

## 3. Build the APK

```bash
cd capacitor
npm install                 # already done
npx cap add android         # scaffolds the android/ Gradle project (once)
npx cap sync android        # copies config + plugins

# Option A — Android Studio (recommended):
npx cap open android        # opens the project; Build ▸ Build APK(s)

# Option B — command line (needs ANDROID_HOME + JDK on PATH):
cd android
./gradlew assembleDebug     # Windows: gradlew.bat assembleDebug
```

The signed-debug APK is produced at:

```
capacitor/android/app/build/outputs/apk/debug/app-debug.apk
```

Copy that file to your phone (or `adb install app-debug.apk`) and install it.

## 4. Release build (Play Store)

```bash
cd android
./gradlew bundleRelease      # AAB for Play Store
# or assembleRelease for a signed APK (configure signing in android/app/build.gradle)
```

## App identity

| Field | Value |
|-------|-------|
| App ID | `com.saintglobal.verify` |
| App name | Saint Globle Verify |
| Splash / theme | Brand azure `#2ca0d4` |
| Native plugins | App, Geolocation, Splash Screen, Status Bar |

## Notes

- The QR scanner runs in the web layer (`BarcodeDetector`), so no native barcode plugin is
  required; on devices without that API the app falls back to manual code entry.
- For native camera permissions, Capacitor adds `CAMERA` to the Android manifest when a
  camera plugin is present — the WebView `getUserMedia` prompt covers scanning here.
