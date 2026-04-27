(function (global) {
  if (global.SQLSMSPasskey) {
    return;
  }

  function base64urlToBuffer(base64url) {
    var base64 = String(base64url || '').replace(/-/g, '+').replace(/_/g, '/');
    var pad = base64.length % 4;
    if (pad) base64 += new Array(5 - pad).join('=');
    var bin = atob(base64);
    var buf = new Uint8Array(bin.length);
    for (var i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
    return buf.buffer;
  }

  function bufferToBase64url(buffer) {
    var bytes = new Uint8Array(buffer);
    var bin = '';
    for (var i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
    return btoa(bin).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  }

  function transformGetOptions(options) {
    var pk = options.publicKey;
    pk.challenge = base64urlToBuffer(pk.challenge);
    if (pk.allowCredentials && pk.allowCredentials.length) {
      pk.allowCredentials = pk.allowCredentials.map(function (c) {
        return {
          type: 'public-key',
          id: base64urlToBuffer(c.id),
          transports: c.transports || []
        };
      });
    }
    return options;
  }

  function transformCreateOptions(options) {
    var pk = options.publicKey;
    pk.challenge = base64urlToBuffer(pk.challenge);
    pk.user.id = base64urlToBuffer(pk.user.id);
    if (pk.excludeCredentials && pk.excludeCredentials.length) {
      pk.excludeCredentials = pk.excludeCredentials.map(function (c) {
        return {
          type: 'public-key',
          id: base64urlToBuffer(c.id),
          transports: c.transports || []
        };
      });
    }
    return options;
  }

  function applyCreatePreference(options, preference) {
    var pk = options.publicKey || {};
    pk.authenticatorSelection = pk.authenticatorSelection || {};

    if (preference === 'phone') {
      pk.hints = ['hybrid'];
      pk.authenticatorSelection.authenticatorAttachment = 'cross-platform';
    } else {
      pk.hints = ['client-device'];
      pk.authenticatorSelection.authenticatorAttachment = 'platform';
    }

    return options;
  }

  function readCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content') || '';
    var input = document.querySelector('input[name="_token"]');
    return input ? input.value : '';
  }

  function parseJsonResponse(response) {
    return response.text().then(function (text) {
      var data = {};
      try {
        data = text ? JSON.parse(text) : {};
      } catch (e) {
        data = {};
      }

      return {
        ok: response.ok,
        status: response.status,
        data: data
      };
    });
  }

  function createCancelledError() {
    var err = new Error('Passkey registration was cancelled.');
    err.cancelled = true;
    return err;
  }

  function defaultNickname(preference) {
    return preference === 'phone' ? 'Phone passkey' : 'This device';
  }

  function register(config) {
    var preference = config && config.preference === 'phone' ? 'phone' : 'device';
    var optionsUrl = String((config && config.optionsUrl) || '');
    var verifyUrl = String((config && config.verifyUrl) || '');
    var csrfToken = String((config && config.csrfToken) || readCsrfToken());
    var nicknameProvider = config && typeof config.getNickname === 'function'
      ? config.getNickname
      : null;
    var nickname = nicknameProvider ? nicknameProvider(preference) : defaultNickname(preference);

    if (!global.PublicKeyCredential) {
      return Promise.reject(new Error('Passkeys are not supported in this browser.'));
    }

    if (nickname === null) {
      return Promise.reject(createCancelledError());
    }

    nickname = String(nickname || '').trim() || defaultNickname(preference);

    return fetch(optionsUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin',
      body: JSON.stringify({})
    })
      .then(parseJsonResponse)
      .then(function (result) {
        if (!result.ok) {
          throw new Error((result.data && result.data.error) ? result.data.error : 'Could not start registration.');
        }

        var createOptions = applyCreatePreference(transformCreateOptions(result.data), preference);
        return global.navigator.credentials.create({ publicKey: createOptions.publicKey });
      })
      .then(function (cred) {
        if (!cred) {
          throw new Error('No credential created.');
        }

        return fetch(verifyUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            nickname: nickname,
            clientDataJSON: bufferToBase64url(cred.response.clientDataJSON),
            attestationObject: bufferToBase64url(cred.response.attestationObject)
          })
        });
      })
      .then(parseJsonResponse)
      .then(function (result) {
        if (!result.ok) {
          throw new Error((result.data && result.data.error) ? result.data.error : 'Registration failed.');
        }

        return result.data || {};
      });
  }

  global.SQLSMSPasskey = {
    applyCreatePreference: applyCreatePreference,
    base64urlToBuffer: base64urlToBuffer,
    bufferToBase64url: bufferToBase64url,
    readCsrfToken: readCsrfToken,
    register: register,
    transformCreateOptions: transformCreateOptions,
    transformGetOptions: transformGetOptions
  };
})(window);
