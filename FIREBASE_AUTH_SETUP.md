# Firebase Email Verification Setup

This project uses Firebase Authentication for Google sign-in and email verification. Firebase ID tokens are verified by PHP before the shop's existing customer session is created. The existing email/password login remains available.

## Firebase Console

1. In **Authentication > Sign-in method**, enable both **Email/Password** and **Google**. Email/Password is needed for the existing registration and password login flow.
2. In **Authentication > Settings > Authorized domains**, add `localhost` for local development and the site's production hostname before deployment.
3. In **Authentication > Templates > Email address verification**, set the custom action URL to the public URL for this site's `verify.php`, for example `https://your-site.example/verify.php`.
4. In **Project settings > General**, open the web app config. Replace `YOUR_API_KEY` and `YOUR_APP_ID` in `firebase.js` with that app's actual values. Set the same Web API key as the `FIREBASE_API_KEY` environment variable for PHP. The web API key is client configuration, not a password; do not expose service-account credentials.
5. Ensure the PHP runtime has the cURL extension enabled.

Firebase sends the email from its managed sender, so this flow does not require a personal email-sending domain. The verification link must reach the deployed site, so `verify.php` needs to be publicly accessible. A local-only `localhost` site cannot receive clicks from a user's Gmail inbox.

## Database And Testing

Run `database/email-verification-migration.sql` once against the site's database before deploying the updated PHP files. Existing customers remain verified; new registrations are inserted as unverified. Admin-disabled accounts remain disabled because verification does not change `cust_status`.

Test with a new `@gmail.com` or `@tip.edu.ph` address: email/password registration should send a Firebase verification email and block login until verified. On the login page, Google sign-in should create or load the local customer and redirect to the dashboard. An address from another domain should be rejected by the PHP Google sign-in handler.

The allowed-domain restriction checks the address suffix only. Firebase email verification confirms access to that mailbox; it does not establish a person's identity or guarantee future deliverability.