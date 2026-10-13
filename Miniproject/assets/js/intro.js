// ============================================================
//  CarVerse 3D | Intro Animation + Hero 3D
//  assets/js/intro.js
// ============================================================

/* ── Intro Canvas Star Field ── */
(function () {
  const canvas = document.getElementById('intro-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  canvas.width  = window.innerWidth;
  canvas.height = window.innerHeight;

  const stars = Array.from({ length: 200 }, () => ({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    r: Math.random() * 1.2 + 0.2,
    a: Math.random(),
    da: (Math.random() - 0.5) * 0.02,
  }));

  function drawStars() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    stars.forEach(s => {
      s.a = Math.max(0.05, Math.min(1, s.a + s.da));
      if (s.a <= 0.05 || s.a >= 1) s.da *= -1;
      ctx.beginPath();
      ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(232,184,75,${s.a})`;
      ctx.fill();
    });
    if (!document.getElementById('intro-overlay')?.classList.contains('hidden')) {
      requestAnimationFrame(drawStars);
    }
  }
  drawStars();
})();

/* ── Auto hide intro after 3s ── */
window.addEventListener('DOMContentLoaded', () => {
  setTimeout(skipIntro, 3200);
});

function skipIntro() {
  const overlay = document.getElementById('intro-overlay');
  if (overlay) overlay.classList.add('hidden');
  // Start hero 3D after intro
  initHero3D();
  // Trigger scroll animations
  document.querySelectorAll('[data-aos]').forEach((el, i) => {
    setTimeout(() => el.classList.add('aos-animate'), 100 + i * 60);
  });
  // Start counters
  document.querySelectorAll('[data-counter]').forEach((el, i) => {
    setTimeout(() => animateCounter(el), 400 + i * 100);
  });
}

/* ── Hero 3D Scene ── */
function initHero3D() {
  const canvas = document.getElementById('hero-canvas');
  if (!canvas || typeof THREE === 'undefined') return;

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(canvas.clientWidth, canvas.clientHeight);
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.2;

  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(50, canvas.clientWidth / canvas.clientHeight, 0.1, 200);
  camera.position.set(0, 1.5, 6);
  camera.lookAt(0, 0, 0);

  /* Lights */
  scene.add(new THREE.AmbientLight(0xffffff, 0.3));
  const key = new THREE.DirectionalLight(0xe8b84b, 2);
  key.position.set(4, 6, 4);
  scene.add(key);
  const fill = new THREE.DirectionalLight(0x1a6cf5, 0.8);
  fill.position.set(-4, 2, -4);
  scene.add(fill);
  const rim = new THREE.PointLight(0xe8b84b, 1, 20);
  rim.position.set(0, -2, 0);
  scene.add(rim);

  /* Reflective floor */
  const floorGeo = new THREE.PlaneGeometry(20, 20);
  const floorMat = new THREE.MeshStandardMaterial({
    color: 0x050810,
    metalness: 0.9,
    roughness: 0.1,
    envMapIntensity: 1,
  });
  const floor = new THREE.Mesh(floorGeo, floorMat);
  floor.rotation.x = -Math.PI / 2;
  floor.position.y = -1.2;
  scene.add(floor);

  /* Build Car Body (simplified placeholder) */
  const carGroup = new THREE.Group();

  // Body
  const bodyGeo = new THREE.BoxGeometry(3.8, 0.8, 1.8);
  const carMat  = new THREE.MeshStandardMaterial({ color: 0x1a1a2e, metalness: 0.9, roughness: 0.1 });
  const body    = new THREE.Mesh(bodyGeo, carMat);
  body.position.y = 0;
  carGroup.add(body);

  // Cabin
  const cabinGeo = new THREE.BoxGeometry(2.2, 0.65, 1.6);
  const cabin    = new THREE.Mesh(cabinGeo, new THREE.MeshStandardMaterial({ color: 0x0d0d1a, metalness: 0.5, roughness: 0.2 }));
  cabin.position.set(-0.1, 0.7, 0);
  carGroup.add(cabin);

  // Gold trim
  const trimGeo = new THREE.BoxGeometry(3.82, 0.06, 1.82);
  const trimMat = new THREE.MeshStandardMaterial({ color: 0xe8b84b, metalness: 1, roughness: 0.0, emissive: 0xe8b84b, emissiveIntensity: 0.3 });
  const trim    = new THREE.Mesh(trimGeo, trimMat);
  trim.position.y = 0.4;
  carGroup.add(trim);

  // Wheels (4 cylinders)
  const wheelGeo = new THREE.CylinderGeometry(0.38, 0.38, 0.25, 32);
  const wheelMat = new THREE.MeshStandardMaterial({ color: 0x111111, metalness: 0.6, roughness: 0.3 });
  const wheelPos = [[-1.4, -0.38, 1.0],[-1.4, -0.38,-1.0],[1.4, -0.38, 1.0],[1.4,-0.38,-1.0]];
  wheelPos.forEach(([x, y, z]) => {
    const w = new THREE.Mesh(wheelGeo, wheelMat);
    w.position.set(x, y, z);
    w.rotation.x = Math.PI / 2;
    carGroup.add(w);
    // Hub
    const hubGeo = new THREE.CylinderGeometry(0.15, 0.15, 0.26, 16);
    const hub = new THREE.Mesh(hubGeo, trimMat);
    hub.position.set(x, y, z);
    hub.rotation.x = Math.PI / 2;
    carGroup.add(hub);
  });

  // Headlights
  const headGeo = new THREE.BoxGeometry(0.08, 0.2, 0.5);
  const headMat = new THREE.MeshStandardMaterial({ color: 0xffffff, emissive: 0xffffff, emissiveIntensity: 1 });
  [-0.6, 0.6].forEach(z => {
    const h = new THREE.Mesh(headGeo, headMat);
    h.position.set(1.93, 0.1, z);
    carGroup.add(h);
    const light = new THREE.PointLight(0xffffff, 0.5, 5);
    light.position.set(2.2, 0.1, z);
    scene.add(light);
  });

  carGroup.position.y = 0.3;
  scene.add(carGroup);

  /* Floating particles around car */
  const pts = [];
  for (let i = 0; i < 60; i++) {
    const theta = Math.random() * Math.PI * 2;
    const r = 3 + Math.random() * 2;
    pts.push(r * Math.cos(theta), (Math.random() - 0.5) * 3, r * Math.sin(theta));
  }
  const ptGeo = new THREE.BufferGeometry();
  ptGeo.setAttribute('position', new THREE.Float32BufferAttribute(pts, 3));
  const ptMat = new THREE.PointsMaterial({ color: 0xe8b84b, size: 0.05, transparent: true, opacity: 0.7 });
  scene.add(new THREE.Points(ptGeo, ptMat));

  /* Animation loop */
  let t = 0;
  function animate() {
    requestAnimationFrame(animate);
    t += 0.008;
    carGroup.rotation.y = Math.sin(t * 0.5) * 0.5 + t * 0.2;
    carGroup.position.y = 0.3 + Math.sin(t) * 0.06;
    rim.intensity = 0.8 + Math.sin(t * 3) * 0.2;
    renderer.render(scene, camera);
  }

  window.addEventListener('resize', () => {
    camera.aspect = canvas.clientWidth / canvas.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(canvas.clientWidth, canvas.clientHeight);
  });
  animate();
}
