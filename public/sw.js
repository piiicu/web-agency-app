self.addEventListener('install', e=>{
  e.waitUntil(
    caches.open('agency-cache').then(c=>{
      return c.addAll(['/dashboard','/chat','/tasks']);
    })
  );
});

self.addEventListener('fetch', e=>{
  e.respondWith(
    caches.match(e.request).then(r=>r||fetch(e.request))
  );
});

