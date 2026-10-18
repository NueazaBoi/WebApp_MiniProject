/* ============================================================
   CarVerse 3D | Particle Assemble Effect (v3 - ทุกหน้า)
   assets/js/particles.js
   ต้องใช้คู่กับ assets/css/particles.css
   - หัวข้อ (.page-header h1 / .auth-heading h1): จุดลอยขึ้นมาประกอบเป็นตัวอักษร
   - การ์ด/พาเนล/ตาราง ฯลฯ: เปิดเผยจากล่างขึ้นบนด้วยเส้นสแกน + ประกายจุด
     เลื่อนลงแล้วเจนทีละส่วน เลื่อนขึ้นกลับไปแสดงทันที
   ============================================================ */
(() => {
  'use strict';

  const root = document.documentElement;
  if (!root.classList.contains('pfx-on')) return;   // reduced-motion / ไม่ได้เปิดใช้
  window.__pfx = true;

  /* รายการ selector ต้องตรงกับใน particles.css */
  const SEL = [
    '.filter-sidebar', '.car-card', '.result-meta', '.pagination',
    '.t-card', '.t-item', '.t-empty', '.stat-card', '.stat-chip',
    '.booking-card', '.empty-state', '.panel', '.toolbar',
    '.profile-card', '.panel-box', '.info-panel', '.car-image-wrap',
    '.desc-section', '.booking-row',
    '.auth-feature', '.auth-form-inner .form-group', '.auth-switch', '.demo-hint'
  ].join(',');

  const CFG = {
    colors: ['#1fc8ff', '#5ee2ff', '#ffffff', '#0b86d6'],
    accent: '#ff9b3d',
    accentChance: 0.08,
    textGap: 2.2,
    mouseRadius: 100,
    mouseForce: 7,
    dustCount: 110,
    revealMs: 750
  };

  const heading = document.querySelector('.page-header h1, .auth-heading h1');
  const header = document.querySelector('.page-header');
  const blocks = [...document.querySelectorAll(SEL)].filter(el => !el.closest('.modal-overlay'));

  // พื้นหลังมืดนุ่มๆ หลังหัวข้อ ให้จุดสว่างเด่นขึ้น
  if (header && heading) {
    const back = document.createElement('div');
    Object.assign(back.style, {
      position: 'absolute', inset: '0', pointerEvents: 'none', zIndex: '0',
      background: 'radial-gradient(ellipse 55% 75% at 50% 42%, rgba(3,8,20,0.9) 0%, rgba(3,8,20,0.55) 45%, rgba(3,8,20,0) 78%)'
    });
    header.insertBefore(back, header.firstChild);
  }

  /* ---------- Canvas ---------- */
  const canvas = document.createElement('canvas');
  canvas.setAttribute('aria-hidden', 'true');
  Object.assign(canvas.style, {
    position: 'fixed', inset: '0', width: '100%', height: '100%',
    pointerEvents: 'none',
    zIndex: document.body.classList.contains('auth-body') ? '50' : '1'
  });
  document.body.appendChild(canvas);
  const ctx = canvas.getContext('2d');
  let W = 0, H = 0;

  function resize() {
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    W = innerWidth; H = innerHeight;
    canvas.width = W * dpr; canvas.height = H * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }
  resize();

  const rand = (a, b) => a + Math.random() * (b - a);
  const pickColor = () =>
    Math.random() < CFG.accentChance
      ? CFG.accent
      : CFG.colors[(Math.random() * CFG.colors.length) | 0];

  const mouse = { x: -9999, y: -9999 };
  addEventListener('pointermove', e => { mouse.x = e.clientX; mouse.y = e.clientY; }, { passive: true });
  document.addEventListener('mouseleave', () => { mouse.x = mouse.y = -9999; });

  /* ---------- Heading → particles ---------- */
  let parts = [];

  async function buildText(first) {
    if (!heading) return;
    try {
      if (document.fonts && document.fonts.ready) await document.fonts.ready;
      parts = [];

      const cs = getComputedStyle(heading);
      const rect = heading.getBoundingClientRect();
      const text = heading.textContent.replace(/\s+/g, ' ').trim();
      if (!rect.width || !text) { heading.style.opacity = '1'; return; }

      let fontSize = parseFloat(cs.fontSize);
      const w = Math.ceil(rect.width);
      const h = Math.ceil(fontSize * 1.7);

      const al = cs.textAlign;
      const mode = al === 'center' ? 'center' : (al === 'right' || al === 'end') ? 'right' : 'left';
      const ax = mode === 'center' ? w / 2 : mode === 'right' ? w : 0;

      const off = document.createElement('canvas');
      off.width = w; off.height = h;
      const c = off.getContext('2d', { willReadFrequently: true });
      const setFont = () => { c.font = `${cs.fontWeight} ${fontSize}px ${cs.fontFamily}`; };
      c.textAlign = mode; c.textBaseline = 'middle';
      if (cs.textTransform === 'uppercase') { /* ใช้ข้อความตามที่แสดงจริง */ }
      if ('letterSpacing' in c && cs.letterSpacing !== 'normal') c.letterSpacing = cs.letterSpacing;

      const shown = cs.textTransform === 'uppercase' ? text.toUpperCase() : text;
      setFont();
      const maxW = w * 0.96;
      let measured = c.measureText(shown).width;
      if (measured > maxW) { fontSize *= maxW / measured; setFont(); measured = maxW; }

      const x0 = mode === 'center' ? w / 2 - measured / 2 : mode === 'right' ? w - measured : 0;
      const g = c.createLinearGradient(x0, 0, x0 + Math.max(measured, 1), 0);
      g.addColorStop(0, '#ffffff');
      g.addColorStop(0.5, '#8eeaff');
      g.addColorStop(1, '#1fc8ff');
      c.fillStyle = g;
      c.fillText(shown, ax, h / 2);

      const data = c.getImageData(0, 0, w, h).data;
      const gap = w < 520 ? 1.8 : CFG.textGap;
      const ox = rect.left + scrollX;
      const oy = rect.top + scrollY + (rect.height - h) / 2;
      const now = performance.now();

      for (let y = 0; y < h; y += gap) {
        for (let x = 0; x < w; x += gap) {
          const i = ((y | 0) * w + (x | 0)) * 4;
          if (data[i + 3] < 140) continue;
          const tx = ox + x + rand(-0.5, 0.5);
          const ty = oy + y + rand(-0.5, 0.5);
          const fx = (x - x0) / Math.max(measured, 1);
          parts.push({
            x: first ? tx + rand(-120, 120) : tx,
            y: first ? scrollY + H + rand(20, 260) : ty,
            tx, ty, vx: 0, vy: 0, fx,
            s: rand(1.5, 2.5),
            c: Math.random() < 0.05 ? CFG.accent : `rgb(${data[i]},${data[i + 1]},${data[i + 2]})`,
            k: rand(0.055, 0.1),
            t0: first ? now + Math.max(0, fx) * 450 + rand(0, 250) : now,
            a: first ? 0 : 1,
            ph: rand(0, 6.28)
          });
        }
      }
    } catch (err) {
      console.warn('[particles] text build failed', err);
      heading.style.opacity = '1';
    }
  }

  /* ---------- Scan reveal ---------- */
  const reveals = [];
  const sparks = [];

  function startReveal(el, delay) {
    if (el.__pfx === 'revealing') return;
    el.__pfx = 'revealing';
    reveals.push({ el, t0: performance.now() + delay });
  }

  function showNow(el) {
    el.style.clipPath = '';
    el.classList.remove('pfx-run');
    el.classList.add('pfx-shown');
    el.__pfx = 'shown';
  }

  blocks.forEach(b => { b.__pfx = 'hidden'; });

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      let n = 0;
      entries.forEach(en => {
        const el = en.target;
        if (en.isIntersecting) {
          if (el.__pfx !== 'hidden') return;
          // เลื่อนขึ้นมาจากด้านบน → แสดงทันที / เลื่อนลงมาจากด้านล่าง → เจนขึ้นมา
          if (en.boundingClientRect.top < 0) showNow(el);
          else startReveal(el, n++ * 90);
        } else if (el.__pfx === 'shown') {
          el.__pfx = 'hidden';
          el.classList.remove('pfx-shown');
        }
      });
    }, { threshold: 0.06, rootMargin: '0px 0px -6% 0px' });
    blocks.forEach(b => io.observe(b));
  } else {
    blocks.forEach(showNow);
  }

  /* ---------- Ambient dust ---------- */
  const dust = Array.from({ length: CFG.dustCount }, () => ({
    x: rand(0, W), y: rand(0, H), s: rand(0.8, 2),
    v: rand(0.08, 0.4), ph: rand(0, 6.28)
  }));

  function dot(x, y, s, color, alpha) {
    ctx.fillStyle = color;
    ctx.globalAlpha = alpha * 0.16;
    ctx.fillRect(x - 2, y - 2, s + 4, s + 4);
    ctx.globalAlpha = alpha;
    ctx.fillRect(x, y, s, s);
  }

  /* ---------- Loop ---------- */
  const R = CFG.mouseRadius, R2 = R * R;
  const ease = t => 1 - Math.pow(1 - t, 3);

  function frame(now) {
    ctx.clearRect(0, 0, W, H);
    const sx = scrollX, sy = scrollY;

    for (const d of dust) {
      d.y -= d.v; d.x += Math.sin(now / 2000 + d.ph) * 0.15;
      if (d.y < -4) { d.y = H + 4; d.x = rand(0, W); }
      ctx.fillStyle = '#9fefff';
      ctx.globalAlpha = 0.12 + 0.4 * (0.5 + 0.5 * Math.sin(now / 700 + d.ph));
      ctx.fillRect(d.x, d.y, d.s, d.s);
    }

    const wave = ((now / 2600) % 1.7) - 0.35;
    for (const p of parts) {
      if (now < p.t0) continue;

      p.vx += (p.tx - p.x) * p.k;
      p.vy += (p.ty - p.y) * p.k;

      const dx = (p.x - sx) - mouse.x, dy = (p.y - sy) - mouse.y;
      const d2 = dx * dx + dy * dy;
      if (d2 < R2 && d2 > 0.01) {
        const d = Math.sqrt(d2), f = (1 - d / R) * CFG.mouseForce;
        p.vx += (dx / d) * f; p.vy += (dy / d) * f;
      }
      p.vx *= 0.82; p.vy *= 0.82;
      p.x += p.vx; p.y += p.vy;
      if (p.a < 1) p.a = Math.min(1, p.a + 0.05);

      const px = p.x - sx, py = p.y - sy;
      if (px < -10 || px > W + 10 || py < -10 || py > H + 10) continue;

      const shimmer = Math.max(0, 1 - Math.abs(p.fx - wave) / 0.09);
      const tw = 0.88 + 0.12 * Math.sin(now / 450 + p.ph);
      const alpha = Math.min(1, p.a * tw + shimmer * 0.5);
      dot(px, py, p.s * (1 + shimmer * 0.7), shimmer > 0.35 ? '#ffffff' : p.c, alpha);
    }

    for (let i = reveals.length - 1; i >= 0; i--) {
      const r = reveals[i];
      if (now < r.t0) continue;
      const t = Math.min(1, (now - r.t0) / CFG.revealMs);
      const e = ease(t);
      const b = r.el.getBoundingClientRect();

      if (t < 1) {
        if (!r.el.classList.contains('pfx-run')) r.el.classList.add('pfx-run');
        r.el.style.clipPath = `inset(${((1 - e) * 100).toFixed(2)}% 0 0 0)`;
        const lineY = b.top + b.height * (1 - e);

        const grad = ctx.createLinearGradient(b.left, 0, b.right, 0);
        grad.addColorStop(0, 'rgba(31,200,255,0)');
        grad.addColorStop(0.5, 'rgba(160,240,255,1)');
        grad.addColorStop(1, 'rgba(31,200,255,0)');
        ctx.fillStyle = grad;
        ctx.globalAlpha = 0.18; ctx.fillRect(b.left, lineY - 6, b.width, 12);
        ctx.globalAlpha = 0.9; ctx.fillRect(b.left, lineY - 1, b.width, 2);

        const n = Math.max(1, Math.ceil(b.width / 20));
        for (let k = 0; k < n; k++) {
          sparks.push({
            x: b.left + Math.random() * b.width, y: lineY + rand(-2, 2),
            vx: rand(-0.35, 0.35), vy: -rand(0.5, 2.4),
            a: 1, s: rand(1.4, 2.6), c: pickColor()
          });
        }
      } else {
        showNow(r.el);
        reveals.splice(i, 1);
      }
    }

    for (let i = sparks.length - 1; i >= 0; i--) {
      const s = sparks[i];
      s.x += s.vx; s.y += s.vy; s.vy *= 0.992; s.a -= 0.02;
      if (s.a <= 0) { sparks.splice(i, 1); continue; }
      dot(s.x, s.y, s.s, s.c, s.a);
    }

    ctx.globalAlpha = 1;
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);

  /* ---------- Init ---------- */
  buildText(true);

  let rt;
  addEventListener('resize', () => {
    resize();
    clearTimeout(rt);
    rt = setTimeout(() => buildText(false), 250);
  });
})();