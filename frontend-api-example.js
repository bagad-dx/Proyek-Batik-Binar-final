// Contoh integrasi frontend statis (index.html / login.html / register.html)
// ke backend Laravel di http://127.0.0.1:8000
const API = 'http://127.0.0.1:8000/api';

async function apiRegister(name, email, phone, password, password_confirmation, channel = 'email') {
  // Langkah 1: minta kode OTP 6 digit (dikirim nyata ke Email/Gmail atau WhatsApp)
  const req = await fetch(`${API}/otp/request`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ name, email, phone, password, password_confirmation, channel }),
  });
  const reqData = await req.json();
  if (!req.ok) throw new Error(reqData.message || 'Gagal mengirim kode OTP');
  // { message, email, channel, via, sent:true, expires_in, resend_in }
  // Kode TIDAK dikembalikan ke browser; user membacanya di email/WhatsApp.
  return reqData;
}

async function apiVerifyOtp(email, code) {
  // Langkah 2: verifikasi kode -> akun dibuat + token diterima
  const res = await fetch(`${API}/otp/verify`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email, code }),
  });
  const data = await res.json();
  if (!res.ok) throw data;
  localStorage.setItem('token', data.token);
  localStorage.setItem('user', JSON.stringify(data.user));
  return data;
}

async function apiResendOtp(email, channel) {
  const res = await fetch(`${API}/otp/resend`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email, channel }),
  });
  const data = await res.json();
  if (!res.ok) throw data;
  return data;
}

async function apiLogin(email, password) {
  const res = await fetch(`${API}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email, password }),
  });
  const data = await res.json();
  if (!res.ok) throw data;
  localStorage.setItem('token', data.token);
  localStorage.setItem('user', JSON.stringify(data.user));
  // redirect sesuai role
  const role = data.user.role;
  if (role === 'superadmin') location.href = 'http://127.0.0.1:8000/superadmin/dashboard';
  else if (role === 'admin') location.href = 'http://127.0.0.1:8000/admin/dashboard';
  else location.href = 'http://127.0.0.1:8000/user/dashboard';
  return data;
}

async function apiGetProducts(search = '') {
  const res = await fetch(`${API}/products?search=${encodeURIComponent(search)}`, { headers: { Accept: 'application/json' } });
  return res.json();
}

async function apiGetGalleries() {
  const res = await fetch(`${API}/galleries`, { headers: { Accept: 'application/json' } });
  return res.json();
}

async function apiGetMotifs() {
  const res = await fetch(`${API}/motifs`, { headers: { Accept: 'application/json' } });
  return res.json();
}

async function apiOrder(productId, payload) {
  const res = await fetch(`${API}/orders`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json', Accept: 'application/json',
      Authorization: 'Bearer ' + localStorage.getItem('token'),
    },
    body: JSON.stringify({ product_id: productId, ...payload }),
  });
  const data = await res.json();
  if (!res.ok) throw data;
  return data;
}
