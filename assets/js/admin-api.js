// File: assets/js/admin-api.js
window.AdminAPI = (function () {
  async function request(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const headers = Object.assign(
      {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      options.headers || {}
    );

    const fetchOpts = {
      method,
      headers,
      credentials: 'same-origin',
    };

    if (options.body !== undefined && options.body !== null) {
      if (options.body instanceof FormData) {
        fetchOpts.body = options.body;
      } else if (typeof options.body === 'object') {
        headers['Content-Type'] = 'application/json; charset=utf-8';
        fetchOpts.body = JSON.stringify(options.body);
      } else {
        headers['Content-Type'] = 'application/json; charset=utf-8';
        fetchOpts.body = options.body;
      }
    }

    const res = await fetch(url, fetchOpts);
    const contentType = res.headers.get('content-type') || '';
    const data = contentType.includes('application/json')
      ? await res.json()
      : { success: res.ok, message: await res.text() };

    if (!res.ok || data.success === false) {
      const err = new Error(data.message || `HTTP ${res.status}`);
      err.status = res.status;
      err.data = data;
      throw err;
    }

    return data;
  }

  return {
    get: (url) => request(url, { method: 'GET' }),
    post: (url, body) => request(url, { method: 'POST', body }),
    del: (url, body) => request(url, { method: 'DELETE', body }),
  };
})();