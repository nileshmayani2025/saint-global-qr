# Releases — Saint Globle Verify (Android)

Committed, version-wise APK builds of the Capacitor app. Each APK is a thin
shell that loads the live site (`https://account.gopinathinfotech.com/qr/public/app/login`)
— so most updates ship by deploying the web app; a new APK is only needed when
the native config (URL, name, icon, plugins, version) changes.

| Version | versionCode | Opens | File | Date |
|---------|-------------|-------|------|------|
| 1.0.0   | 1           | `/app/login` (mobile OTP) | [v1.0.0/Saint-Globle-Verify-v1.0.0.apk](v1.0.0/Saint-Globle-Verify-v1.0.0.apk) | 2026-07-18 |

## Install
Debug build — on the phone enable **Install unknown apps** for your file
manager/browser, then open the `.apk`.

## Build a new version
1. Bump `versionCode` / `versionName` in `capacitor/android/app/build.gradle`
   (and `capacitor/package.json` `version`).
2. `cd capacitor && npm run build:android`
3. Copy `capacitor/android/app/build/outputs/apk/debug/app-debug.apk` here as
   `releases/vX.Y.Z/Saint-Globle-Verify-vX.Y.Z.apk` and add a row above.

> Debug APKs are debug-signed. For Play Store / public distribution, produce a
> release build signed with a keystore (`assembleRelease` / `bundleRelease`).
