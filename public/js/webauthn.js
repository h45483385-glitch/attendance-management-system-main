/**
 * WebAuthn / FIDO2 Helper Script (Zero-Storage Biometrics)
 * Windows Hello, Touch ID, & Platform Fingerprint Sensor Integration
 */
const WebAuthnClient = {
    isSupported: function() {
        return !!(window.PublicKeyCredential && navigator.credentials && navigator.credentials.create);
    },

    registerDevice: function(csrfToken, challengeUrl, verifyUrl) {
        if (!this.isSupported()) {
            return Promise.reject(new Error('WebAuthn / Windows Hello is not supported in this browser.'));
        }

        return fetch(challengeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(options => {
            if (!options.challenge) throw new Error(options.message || 'Failed to initialize biometric challenge.');

            options.challenge = Uint8Array.from(atob(options.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
            options.user.id = Uint8Array.from(atob(options.user.id), c => c.charCodeAt(0));

            return navigator.credentials.create({ publicKey: options });
        })
        .then(credential => {
            const payload = {
                id: credential.id,
                rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                type: credential.type,
                response: {
                    attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject))),
                    clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON)))
                }
            };

            return fetch(verifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
        })
        .then(res => res.json());
    }
};
