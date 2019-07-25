/**
 * The API lib acts as a thin wrapper for XHR calls within the app, and allows us to configure and
 * even replace the implementation without manually adjusting every call.
 */
const api = (endpoint, method = 'GET', body = undefined, headers = {}) => (
  fetch(
    endpoint,
    {
      body,
      credentials: 'same-origin',
      headers,
      method,
    },
  )
);

export default api;
