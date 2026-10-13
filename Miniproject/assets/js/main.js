// ============================================================
//  CarVerse 3D | Main JavaScript
//  assets/js/main.js
// ============================================================

/* ── Navbar scroll effect ── */
const navbar = document.querySelector('.navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  });
}

/* ── Particles Background ── */
function initParticles(canvasId = 'particles') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W = canvas.width = window.innerWidth;
  let H = canvas.height = window.innerHeight;

  const particles = Array.from({ length: 80 }, () => ({
    x: Math.random() * W,
    y: Math.random() * H,
    r: Math.random() * 1.5 + 0.3,
    dx: (Math.random() - 0.5) * 0.3,
    dy: (Math.random() - 0.5) * 0.3,
    a: Math.random(),
  }));

  function draw() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(232,184,75,${p.a * 0.6})`;
      ctx.fill();
      p.x += p.dx; p.y += p.dy;
      if (p.x < 0 || p.x > W) p.dx *= -1;
      if (p.y < 0 || p.y > H) p.dy *= -1;
    });
    requestAnimationFrame(draw);
  }

  window.addEventListener('resize', () => {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
  });
  draw();
}

/* ── Intersection Observer – fade-in on scroll ── */
function initScrollAnimations() {
  const elements = document.querySelectorAll('[data-aos]');
  if (!elements.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const delay = el.dataset.aosDelay || 0;
        setTimeout(() => {
          el.classList.add('aos-animate');
        }, parseInt(delay));
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.15 });

  elements.forEach(el => observer.observe(el));
}

// Inline AOS styles
const aosStyle = document.createElement('style');
aosStyle.textContent = `
  [data-aos]           { opacity: 0; transform: translateY(30px); transition: opacity 0.6s ease, transform 0.6s ease; }
  [data-aos].aos-animate { opacity: 1; transform: translateY(0); }
  [data-aos="fade-left"]  { transform: translateX(-30px); }
  [data-aos="fade-left"].aos-animate { transform: translateX(0); }
  [data-aos="fade-right"] { transform: translateX(30px); }
  [data-aos="fade-right"].aos-animate { transform: translateX(0); }
  [data-aos="zoom-in"]    { transform: scale(0.9); }
  [data-aos="zoom-in"].aos-animate   { transform: scale(1); }
`;
document.head.appendChild(aosStyle);

/* ── Counter Animation ── */
function animateCounter(el) {
  const target = parseInt(el.dataset.target, 10);
  const prefix = el.dataset.prefix || '';
  const suffix = el.dataset.suffix || '';
  const dur = 1500;
  const start = performance.now();

  function update(now) {
    const elapsed = now - start;
    const progress = Math.min(elapsed / dur, 1);
    const ease = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
    el.textContent = prefix + Math.round(ease * target).toLocaleString() + suffix;
    if (progress < 1) requestAnimationFrame(update);
  }
  requestAnimationFrame(update);
}

function initCounters() {
  const counters = document.querySelectorAll('[data-counter]');
  if (!counters.length) return;
  const io = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  counters.forEach(el => io.observe(el));
}

/* ── Bar Chart Animation (for Reports) ── */
function initChartBars() {
  document.querySelectorAll('.chart-bar-fill').forEach(bar => {
    const width = bar.style.width;
    bar.style.width = '0';
    setTimeout(() => { bar.style.width = width; }, 400);
  });
}

/* ── Flash Message Auto-hide ── */
function initAlerts() {
  document.querySelectorAll('.alert[data-autohide]').forEach(alert => {
    const delay = parseInt(alert.dataset.autohide) || 4000;
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s, transform 0.5s';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-10px)';
      setTimeout(() => alert.remove(), 500);
    }, delay);
  });
}

/* ── Confirm Delete Modal ── */
function confirmDelete(carId, carName) {
  const modal = document.getElementById('deleteModal');
  if (!modal) return;
  modal.querySelector('#deleteCarName').textContent = carName;
  modal.querySelector('#deleteForm').action = `delete_car.php?id=${carId}`;
  modal.classList.add('active');
}
function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('active');
}

// Modal styles
const modalStyle = document.createElement('style');
modalStyle.textContent = `
  .modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.75); backdrop-filter: blur(8px);
    align-items: center; justify-content: center;
  }
  .modal-overlay.active { display: flex; }
  .modal-box {
    background: #0d1117; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px; padding: 36px; max-width: 420px; width: 90%;
    animation: fadeInUp 0.3s ease;
  }
  .modal-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 12px; }
  .modal-body  { color: #8a8fa8; margin-bottom: 24px; line-height: 1.6; }
  .modal-actions { display: flex; gap: 12px; justify-content: flex-end; }
`;
document.head.appendChild(modalStyle);

/* ── Image Preview on File Input ── */
function initImagePreview() {
  document.querySelectorAll('input[type=file][data-preview]').forEach(input => {
    const previewId = input.dataset.preview;
    const preview = document.getElementById(previewId);
    if (!preview) return;
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  });
}

/* ── Three.js Simple Car Viewer ── */
async function init3DViewer(canvasId = 'three-canvas', modelUrl = null) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof THREE === 'undefined') return;

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  renderer.setPixelRatio(window.devicePixelRatio);
  renderer.setSize(canvas.clientWidth, canvas.clientHeight);
  renderer.shadowMap.enabled = true;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(45, canvas.clientWidth / canvas.clientHeight, 0.1, 100);
  camera.position.set(0, 2, 5);

  // Lights
  scene.add(new THREE.AmbientLight(0xffffff, 0.5));
  const dirLight = new THREE.DirectionalLight(0xe8b84b, 1.2);
  dirLight.position.set(5, 8, 5);
  dirLight.castShadow = true;
  scene.add(dirLight);
  const rimLight = new THREE.DirectionalLight(0x3498db, 0.6);
  rimLight.position.set(-5, 2, -5);
  scene.add(rimLight);

  // Grid floor
  const grid = new THREE.GridHelper(20, 20, 0x333333, 0x222222);
  scene.add(grid);

  let model = null;
  if (modelUrl) {
    const { GLTFLoader } = await import('https://cdn.jsdelivr.net/npm/three@0.163/examples/jsm/loaders/GLTFLoader.js');
    const loader = new GLTFLoader();
    loader.load(modelUrl, gltf => {
      model = gltf.scene;
      const box = new THREE.Box3().setFromObject(model);
      const center = box.getCenter(new THREE.Vector3());
      const size = box.getSize(new THREE.Vector3());
      model.position.sub(center);
      model.position.y = -size.y / 2;
      scene.add(model);
    });
  } else {
    // Demo cube if no model
    const geo = new THREE.BoxGeometry(2, 1, 4);
    const mat = new THREE.MeshStandardMaterial({ color: 0xe8b84b, metalness: 0.8, roughness: 0.2 });
    model = new THREE.Mesh(geo, mat);
    scene.add(model);
  }

  // OrbitControls-like mouse drag
  let isDragging = false, prevX = 0;
  let rotY = 0;
  canvas.addEventListener('mousedown', e => { isDragging = true; prevX = e.clientX; });
  window.addEventListener('mouseup', () => { isDragging = false; });
  window.addEventListener('mousemove', e => {
    if (!isDragging || !model) return;
    rotY += (e.clientX - prevX) * 0.01;
    prevX = e.clientX;
  });

  // Touch
  canvas.addEventListener('touchstart', e => { isDragging = true; prevX = e.touches[0].clientX; });
  window.addEventListener('touchend', () => { isDragging = false; });
  window.addEventListener('touchmove', e => {
    if (!isDragging || !model) return;
    rotY += (e.touches[0].clientX - prevX) * 0.01;
    prevX = e.touches[0].clientX;
  });

  function animate() {
    requestAnimationFrame(animate);
    if (model) {
      if (!isDragging) rotY += 0.005;
      model.rotation.y = rotY;
    }
    renderer.render(scene, camera);
  }
  animate();
}

/* ── Init All ── */
document.addEventListener('DOMContentLoaded', () => {
  initParticles();
  initScrollAnimations();
  initCounters();
  initChartBars();
  initAlerts();
  initImagePreview();

  // แก้ตรงนี้ให้มี assets/ นำหน้าพาร์ทไฟล์ 3D
  init3DViewer('three-canvas', 'assets/models/mclaren_p1.glb');
});