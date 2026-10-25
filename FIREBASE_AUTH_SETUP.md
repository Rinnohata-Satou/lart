# Firebase Email Verification Setup

This project uses Firebase Authentication to create a verification identity and deliver verification emails. The shop's existing PHP password and session login remains in place.

## Firebase Console

1. Create a Firebase project, then enable **Authentication > Sign-in method > Email/Password**.
2. In **Authentication > Templates > Email address verification**, set the custom action URL to the public URL for this site's `verify.php`, for example `https://your-site.example/verify.php`.
3. Use the project's Web API key as the `FIREBASE_API_KEY` environment variable for PHP. Do not commit the key or place it in source code.
4. Ensure the PHP runtime has the cURL extension enabled.

Firebase sends the email from its managed sender, so this flow does not require a personal email-sending domain. The verification link must reach the deployed site, so `verify.php` needs to be publicly accessible. A local-only `localhost` site cannot receive clicks from a user's Gmail inbox.

## Database And Testing

Run `database/email-verification-migration.sql` once against the site's database before deploying the updated PHP files. Existing customers remain verified; new registrations are inserted as unverified. Admin-disabled accounts remain disabled because verification does not change `cust_status`.

Test with a new `@gmail.com` or `@tip.edu.ph` address: registration should send a Firebase verification email, login should be blocked before clicking its link, and verification should allow a subsequent login. An address from another domain should be rejected both by the browser form and the PHP handler.

The allowed-domain restriction checks the address suffix only. Firebase email verification confirms access to that mailbox; it does not establish a person's identity or guarantee future deliverability.