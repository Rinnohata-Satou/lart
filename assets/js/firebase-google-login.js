import { signInWithGoogle } from '../../firebase.js';

const button = document.getElementById('google-sign-in');
const message = document.getElementById('google-auth-message');

if (button && message) {
	button.addEventListener('click', async () => {
		button.disabled = true;
		message.textContent = '';

		try {
			const idToken = await signInWithGoogle();
			const body = new URLSearchParams({
				idToken,
				_csrf: button.dataset.csrf
			});
			const response = await fetch(button.dataset.endpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
				credentials: 'same-origin',
				body
			});
			const result = await response.json();

			if (!response.ok || !result.redirect) {
				throw new Error(result.error || 'Google sign-in could not be completed.');
			}

			window.location.assign(result.redirect);
		} catch (error) {
			const messages = {
				'auth/popup-closed-by-user': 'Sign-in was cancelled.',
				'auth/unauthorized-domain': 'This site is not enabled for Firebase sign-in. Add its hostname to Firebase Authorized domains.',
				'auth/account-exists-with-different-credential': 'This email already has a password-based account. Sign in with your email and password instead.'
			};
			message.textContent = messages[error.code] || error.message || 'Google sign-in could not be completed.';
			button.disabled = false;
		}
	});
}