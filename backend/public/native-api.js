/* Compatibility entry point: load the shared runtime and page feature files. */
(function () {
  'use strict';

  const scriptUrl = document.currentScript && document.currentScript.src;
  if (!scriptUrl) throw new Error('Batik Binar native runtime must be loaded from a script element.');
  const version = new URL(scriptUrl).searchParams.get('v');

  const files = [
    'global.js', 'auth.js', 'payment-methods.js', 'shop.js', 'dashboard.js',
    'cart.js', 'wishlist.js', 'profile.js', 'payment.js', 'orders.js',
    'chat.js', 'product.js', 'checkout.js', 'gallery.js',
  ];

  files.forEach((file) => {
    const src = new URL('js/native/' + file, scriptUrl);
    if (version) src.searchParams.set('v', version);
    document.write('<script src="' + src.href + '"><\/script>');
  });
})();