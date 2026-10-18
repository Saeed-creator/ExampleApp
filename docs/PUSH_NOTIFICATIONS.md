# Push notifications (Firebase Cloud Messaging)

Every morning at 8:00 in the **user's own timezone**, users with the app installed get a push:
"Your daily prediction is ready" (in their language). Tapping it should open the prediction screen.

## How it works

```
Flutter app ──POST /api/device-tokens──▶ Laravel (stores the FCM token)
                                              │
 scheduler (hourly) ─▶ predictions:notify ─▶ DailyPredictionReady ─▶ FcmChannel ─▶ FcmService ─▶ Firebase ─▶ phone
```

| Piece | File |
|---|---|
| Token storage | `database/migrations/*_create_device_tokens_table.php`, `app/Models/DeviceToken.php` |
| Register endpoint | `app/Http/Controllers/DeviceTokenController.php` (`POST /api/device-tokens`) |
| FCM client (HTTP v1) | `app/Services/FcmService.php` |
| Laravel channel | `app/Channels/FcmChannel.php` |
| The notification | `app/Notifications/DailyPredictionReady.php` |
| Daily job | `app/Console/Commands/SendDailyPredictionNotifications.php`, scheduled in `app/Console/Kernel.php` |
| Manual test | `php artisan fcm:test <user id or email>` |

Sending more kinds of push later is one new class:
`$user->notify(new SomethingElse(...))` with `via()` returning `[FcmChannel::class]` and a `toFcm()` method.

## 1. Laravel setup

1. Firebase console → your project → **Project settings → Service accounts → Generate new private key**.
2. Save the downloaded file as `storage/app/firebase-credentials.json`. **Never commit it** (it is git-ignored).
   Or keep it elsewhere and set `FIREBASE_CREDENTIALS=/full/path/file.json` in `.env`.
3. Optional in `.env`: `FCM_DAILY_HOUR=8` (local hour of the daily push).
4. `php artisan migrate`
5. Make sure the scheduler runs. On a server, one cron entry:
   `* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`
   (in Docker, run `php artisan schedule:work` as a process).

## 2. Flutter setup

Add `firebase_core` and `firebase_messaging`, run `flutterfire configure`, then:

```dart
import 'dart:convert';
import 'dart:io' show Platform;
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;

// main(): call once at startup
await Firebase.initializeApp();

// Call right after a successful login (you have the Sanctum token), and ask permission.
Future<void> setUpPush(String authToken) async {
  final messaging = FirebaseMessaging.instance;
  await messaging.requestPermission(); // iOS and Android 13+

  Future<void> register(String? fcmToken) async {
    if (fcmToken == null) return;
    await http.post(
      Uri.parse('$baseUrl/api/device-tokens'),
      headers: {
        'Authorization': 'Bearer $authToken',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'token': fcmToken,
        'platform': Platform.isIOS ? 'ios' : 'android',
      }),
    );
  }

  await register(await messaging.getToken());
  messaging.onTokenRefresh.listen(register); // Firebase rotates tokens occasionally
}

// On logout, send the token so this phone stops receiving this user's pushes:
final fcmToken = await FirebaseMessaging.instance.getToken();
await http.post(
  Uri.parse('$baseUrl/api/logout'),
  headers: {'Authorization': 'Bearer $authToken', 'Accept': 'application/json',
            'Content-Type': 'application/json'},
  body: jsonEncode({'device_token': fcmToken}),
);

// Open the right screen when the user taps the push:
void handle(RemoteMessage m) {
  if (m.data['type'] == 'daily_prediction') {
    // navigate to the prediction screen (m.data['prediction_date'] is available)
  }
}
FirebaseMessaging.onMessageOpenedApp.listen(handle);           // app in background
final initial = await FirebaseMessaging.instance.getInitialMessage();
if (initial != null) handle(initial);                           // app was closed
```

Notes:
- **iOS** needs an APNs key uploaded in Firebase console → Project settings → Cloud Messaging.
- When the app is **open in the foreground**, Android does not show the banner by itself.
  Use `FirebaseMessaging.onMessage` with the `flutter_local_notifications` package if you want one.

## 3. Test it

1. Log in on the app (so the token is registered).
2. `php artisan fcm:test you@example.com` → a push should arrive within seconds.
3. Real flow: `php artisan predictions:notify --hour=<current local hour of the user>`.

Invalid or uninstalled tokens are deleted automatically when Firebase reports them as dead.

## Hosting on Railway (no file upload)
1. Firebase console → Project settings → Service accounts → **Generate new private key** (a `.json` file).
2. Turn it into one line of base64 (avoids problems with line breaks):
   - Mac/Linux: `base64 -w0 firebase-credentials.json` (Mac: `base64 -i firebase-credentials.json`)
   - Windows PowerShell: `[Convert]::ToBase64String([IO.File]::ReadAllBytes("firebase-credentials.json"))`
3. Railway → your service → **Variables** → add `FIREBASE_CREDENTIALS_JSON` = that text (raw JSON also works). Redeploy.
4. Run the scheduler: add a **second Railway service** from the same repo with start command
   `php artisan schedule:work` (the web service only runs `artisan serve`).
5. Test: `railway run php artisan fcm:test user@example.com` (or the service's shell).
Never commit the `.json` file or the variable's value.
