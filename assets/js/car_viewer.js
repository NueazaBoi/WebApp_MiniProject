// CarVerse 360° viewer — ภายนอก (หมุนรอบคัน) + ภายใน (มองรอบห้องโดยสาร)
// ใช้ Three.js ผ่าน importmap (ดูวิธีใส่ใน car_detail.php)
import * as THREE from 'three';
import { RoundedBoxGeometry } from 'three/addons/geometries/RoundedBoxGeometry.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { mergeGeometries } from 'three/addons/utils/BufferGeometryUtils.js';

const COLORS = {
  black: 0x0b0b0e, white: 0xf2f2f2, silver: 0xb8bcc2, gray: 0x6b7078, grey: 0x6b7078,
  red: 0xb3121b, blue: 0x1d4ed8, green: 0x1f6b3a, orange: 0xe06a10, yellow: 0xe5b50a,
  brown: 0x5a3a22, gold: 0xc9a24a,
  'ดำ': 0x0b0b0e, 'ขาว': 0xf2f2f2, 'เงิน': 0xb8bcc2, 'เทา': 0x6b7078, 'แดง': 0xb3121b,
  'น้ำเงิน': 0x1d4ed8, 'เขียว': 0x1f6b3a, 'ส้ม': 0xe06a10, 'เหลือง': 0xe5b50a, 'น้ำตาล': 0x5a3a22,
};
function parseColor(c) {
  if (!c) return null;
  c = String(c).trim();
  if (/^#?[0-9a-f]{6}$/i.test(c)) return parseInt(c.replace('#', ''), 16);
  const k = c.toLowerCase();
  for (const n in COLORS) if (k.includes(n)) return COLORS[n];
  return null;
}

// ทำให้โมเดลเบาขึ้น: (1) แปลงวัสดุหนัก (transmission/specular ฯลฯ) เป็นแบบธรรมดา
// (2) รวมชิ้นส่วนที่ใช้วัสดุเดียวกันเป็นก้อนเดียว ลดจำนวนครั้งที่ต้องสั่งวาดจาก ~330 เหลือหลักสิบ
function optimizeCar(src) {
  const cache = new Map();
  const convert = m => {
    if (cache.has(m)) return cache.get(m);
    const glassy = m.transmission > 0 || m.name === 'Windows' || /Glass/.test(m.name);
    const isPaint = m.name === 'Ext_CarPaint';
    const props = {
      name: m.name, color: m.color, map: m.map, roughness: m.roughness, metalness: m.metalness,
      roughnessMap: m.roughnessMap, metalnessMap: m.metalnessMap, normalMap: m.normalMap,
      normalScale: m.normalScale, aoMap: m.aoMap, aoMapIntensity: m.aoMapIntensity,
      emissive: m.emissive, emissiveMap: m.emissiveMap, emissiveIntensity: m.emissiveIntensity,
      alphaMap: m.alphaMap, alphaTest: m.alphaTest, side: m.side, vertexColors: m.vertexColors,
      transparent: m.transparent, opacity: m.opacity, depthWrite: m.depthWrite,
    };
    let n;
    if (isPaint) {
      n = new THREE.MeshPhysicalMaterial({ ...props, clearcoat: 1, clearcoatRoughness: 0.08 });
    } else {
      n = new THREE.MeshStandardMaterial(props);
    }
    if (glassy) {
      n.transparent = true;
      n.opacity = Math.min(m.opacity < 1 ? m.opacity : 0.3, 0.35);
      n.depthWrite = false;
      n.roughness = 0.05;
    }
    cache.set(m, n);
    return n;
  };

  src.updateMatrixWorld(true);
  const groups = new Map();
  const loose = [];
  src.traverse(o => {
    if (!o.isMesh) return;
    const mat = convert(Array.isArray(o.material) ? o.material[0] : o.material);
    const g = o.geometry.clone();
    g.applyMatrix4(o.matrixWorld);
    const key = mat.uuid + '|' + Object.keys(g.attributes).sort().join(',');
    if (!groups.has(key)) groups.set(key, { mat, geos: [] });
    groups.get(key).geos.push(g);
  });
  const out = new THREE.Group();
  groups.forEach(({ mat, geos }) => {
    let merged = null;
    try { merged = mergeGeometries(geos, false); } catch (e) { merged = null; }
    if (merged) {
      const mesh = new THREE.Mesh(merged, mat);
      mesh.renderOrder = mat.transparent ? 2 : 0;
      out.add(mesh);
    } else {
      geos.forEach(g => { const mesh = new THREE.Mesh(g, mat); mesh.renderOrder = mat.transparent ? 2 : 0; out.add(mesh); });
    }
  });
  return out;
}

function injectCSS() {
  if (document.getElementById('c360-css')) return;
  const s = document.createElement('style');
  s.id = 'c360-css';
  s.textContent = `
.c360{position:relative;width:100%;height:100%;min-height:420px;overflow:hidden;border-radius:inherit;cursor:grab;touch-action:none;user-select:none;-webkit-user-select:none}
.c360.grab{cursor:grabbing}
.c360 canvas{width:100%;height:100%;display:block}
.c360-tabs{position:absolute;left:50%;bottom:22px;transform:translateX(-50%);display:flex;gap:6px;padding:5px;border-radius:999px;background:rgba(6,16,28,.65);border:1px solid rgba(34,211,238,.35);backdrop-filter:blur(8px)}
.c360-tabs button{border:0;background:transparent;color:#a9c4d4;font:600 14px inherit;font-family:inherit;padding:9px 20px;border-radius:999px;cursor:pointer;transition:.2s}
.c360-tabs button.on{background:#22d3ee;color:#04202a;box-shadow:0 0 18px rgba(34,211,238,.55)}
.c360-hint{position:absolute;left:50%;top:18px;transform:translateX(-50%);padding:6px 14px;border-radius:999px;font:500 12px inherit;font-family:inherit;color:#cfe9f3;background:rgba(6,16,28,.55);white-space:nowrap;pointer-events:none}
.c360-badge{position:absolute;left:18px;top:18px;padding:6px 12px;border-radius:10px;font:700 13px inherit;font-family:inherit;color:#22d3ee;border:1px solid rgba(34,211,238,.5);background:rgba(6,16,28,.55);pointer-events:none}
.c360-fs{position:absolute;right:18px;bottom:22px;width:40px;height:40px;border-radius:50%;border:1px solid rgba(34,211,238,.35);background:rgba(6,16,28,.65);color:#cfe9f3;font-size:18px;cursor:pointer}
.c360-load{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;color:#cfe9f3;font:600 14px inherit;font-family:inherit;pointer-events:none;transition:opacity .4s}
.c360-load i{display:block;width:180px;height:4px;margin:10px auto 0;border-radius:4px;background:rgba(34,211,238,.2);overflow:hidden}
.c360-load b{display:block;height:100%;width:0;background:#22d3ee;box-shadow:0 0 10px #22d3ee;transition:width .2s}
.c360-note{position:absolute;left:50%;top:60px;transform:translateX(-50%);max-width:90%;padding:6px 14px;border-radius:10px;font:500 12px inherit;font-family:inherit;color:#ffd0d0;background:rgba(120,20,20,.75);border:1px solid rgba(255,100,100,.5);pointer-events:none;text-align:center}
.c360-fade{position:absolute;inset:0;background:#02060c;opacity:0;pointer-events:none;transition:opacity .25s}
.c360-fade.on{opacity:1}
@media(max-width:600px){.c360{min-height:320px}.c360-hint{font-size:11px}}
`;
  document.head.appendChild(s);
}

function bar(p1, p2, r, mat) {
  const a = new THREE.Vector3(...p1), b = new THREE.Vector3(...p2);
  const m = new THREE.Mesh(new THREE.CylinderGeometry(r, r, a.distanceTo(b), 8), mat);
  m.position.copy(a).add(b).multiplyScalar(0.5);
  m.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), b.clone().sub(a).normalize());
  return m;
}
function box(w, h, d, mat, x, y, z) {
  const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat);
  m.position.set(x, y, z);
  return m;
}

// รถ SUV 7 ที่นั่ง (โมเดลสร้างจากโค้ด) — หน้ารถ = +z, พวงมาลัยขวา (ไทย)
function buildCar(paintHex) {
  const car = new THREE.Group();
  const paint = new THREE.MeshPhysicalMaterial({ color: paintHex, metalness: 0.6, roughness: 0.28, clearcoat: 1, clearcoatRoughness: 0.05, side: THREE.DoubleSide });
  const glass = new THREE.MeshPhysicalMaterial({ color: 0x9fd8ee, transparent: true, opacity: 0.2, roughness: 0, metalness: 0, side: THREE.DoubleSide, depthWrite: false });
  const trim = new THREE.MeshStandardMaterial({ color: 0x111214, roughness: 0.7 });
  const chrome = new THREE.MeshStandardMaterial({ color: 0xdddddd, metalness: 1, roughness: 0.15 });
  const fabric = new THREE.MeshStandardMaterial({ color: 0x1b1c21, roughness: 0.95 });
  const leather = new THREE.MeshStandardMaterial({ color: 0x2b2c33, roughness: 0.6 });
  const liner = new THREE.MeshStandardMaterial({ color: 0x70737a, roughness: 1, side: THREE.DoubleSide });
  const headL = new THREE.MeshStandardMaterial({ color: 0xffffff, emissive: 0xffffff, emissiveIntensity: 1.6 });
  const tailL = new THREE.MeshStandardMaterial({ color: 0xff2020, emissive: 0xff1010, emissiveIntensity: 1.4 });
  const screen = new THREE.MeshStandardMaterial({ color: 0x06222b, emissive: 0x22d3ee, emissiveIntensity: 0.9 });

  // ตัวถังล่าง
  const lower = new THREE.Mesh(new RoundedBoxGeometry(1.9, 0.75, 4.8, 4, 0.12), paint);
  lower.position.set(0, 0.775, 0);
  car.add(lower);

  // กระจกห้องโดยสาร (ด้านข้าง-หน้า-หลัง) ขึ้นรูปจากโปรไฟล์ด้านข้าง
  const prof = new THREE.Shape();
  prof.moveTo(1.15, 1.15); prof.lineTo(0.55, 1.85); prof.lineTo(-1.75, 1.85); prof.lineTo(-2.0, 1.15); prof.closePath();
  const gg = new THREE.ExtrudeGeometry(prof, { depth: 1.8, bevelEnabled: false });
  gg.translate(0, 0, -0.9);
  gg.rotateY(-Math.PI / 2);
  car.add(new THREE.Mesh(gg, glass));

  // หลังคา + ฝ้า + เสา
  car.add(box(1.84, 0.06, 2.3, paint, 0, 1.87, -0.6));
  car.add(box(1.7, 0.02, 2.2, liner, 0, 1.83, -0.6));
  for (const s of [-1, 1]) {
    const x = s * 0.9;
    car.add(bar([x, 1.15, 1.15], [x, 1.85, 0.55], 0.04, paint));
    car.add(bar([x, 1.15, -0.45], [x, 1.85, -0.45], 0.04, paint));
    car.add(bar([x, 1.15, -1.95], [x, 1.85, -1.75], 0.05, paint));
    car.add(bar([x, 1.86, 0.55], [x, 1.86, -1.75], 0.035, chrome));
    // แผงประตูด้านใน
    car.add(box(0.05, 0.35, 2.9, leather, s * 0.86, 1.325, -0.45));
    // กระจกมองข้าง
    car.add(box(0.05, 0.05, 0.12, trim, s * 0.97, 1.28, 1.0));
    car.add(box(0.12, 0.14, 0.2, paint, s * 1.04, 1.34, 0.98));
    // ไฟหน้า-ท้าย
    car.add(box(0.42, 0.14, 0.06, headL, s * 0.65, 0.98, 2.4));
    car.add(box(0.3, 0.2, 0.06, tailL, s * 0.72, 1.0, -2.4));
  }
  // พื้นห้องโดยสาร
  car.add(box(1.7, 0.01, 3.0, fabric, 0, 1.158, -0.4));

  // กระจังหน้า / กันชน
  car.add(box(1.2, 0.3, 0.06, trim, 0, 0.92, 2.4));
  car.add(box(1.0, 0.04, 0.07, chrome, 0, 0.98, 2.42));
  car.add(box(1.0, 0.04, 0.07, chrome, 0, 0.88, 2.42));
  car.add(box(1.86, 0.2, 0.1, trim, 0, 0.52, 2.38));
  car.add(box(1.86, 0.2, 0.1, trim, 0, 0.52, -2.38));

  // ล้อ
  for (const sx of [-1, 1]) for (const z of [1.5, -1.5]) {
    const w = new THREE.Group();
    const tire = new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.42, 0.3, 32).rotateZ(Math.PI / 2), trim);
    const rim = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.28, 0.31, 24).rotateZ(Math.PI / 2), chrome);
    w.add(tire, rim);
    for (let i = 0; i < 5; i++) {
      const sp = box(0.32, 0.06, 0.04, trim, 0, 0, 0);
      sp.rotation.x = (i / 5) * Math.PI;
      sp.position.x = sx * 0.155;
      w.add(sp);
    }
    w.position.set(sx * 0.88, 0.42, z);
    car.add(w);
  }

  // เบาะ
  function seat(x, z, w) {
    const g = new THREE.Group();
    g.add(box(w, 0.14, 0.5, leather, 0, 1.25, 0));
    const back = box(w, 0.5, 0.12, leather, 0, 1.5, -0.22);
    back.rotation.x = -0.12;
    g.add(back);
    g.add(box(0.22, 0.14, 0.1, leather, 0, 1.72, -0.26));
    g.position.set(x, 0, z);
    return g;
  }
  car.add(seat(-0.45, 0.2, 0.5), seat(0.45, 0.2, 0.5));
  for (const x of [-0.6, 0, 0.6]) car.add(seat(x, -0.65, 0.55));
  for (const x of [-0.45, 0.45]) car.add(seat(x, -1.35, 0.5));

  // แดชบอร์ด / คอนโซล / พวงมาลัย (ขวา)
  car.add(box(1.72, 0.3, 0.5, fabric, 0, 1.3, 0.88));
  car.add(box(1.72, 0.06, 0.52, leather, 0, 1.46, 0.88));
  const scr = box(0.38, 0.22, 0.03, screen, 0, 1.55, 0.66);
  scr.rotation.x = -0.25;
  car.add(scr);
  car.add(box(0.32, 0.1, 0.02, screen, -0.45, 1.53, 0.66));
  car.add(box(0.3, 0.22, 1.0, leather, 0, 1.27, 0.15));
  const knob = new THREE.Mesh(new THREE.SphereGeometry(0.04, 12, 12), chrome);
  knob.position.set(0, 1.42, 0.35);
  car.add(knob);
  const wheel = new THREE.Mesh(new THREE.TorusGeometry(0.17, 0.024, 12, 36), trim);
  wheel.position.set(-0.45, 1.42, 0.62);
  wheel.rotation.x = 0.52;
  car.add(wheel);
  const hub = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.05, 0.03, 16).rotateX(Math.PI / 2), chrome);
  hub.position.copy(wheel.position);
  hub.rotation.x = 0.52;
  car.add(hub);

  car.traverse(o => { if (o.isMesh) { o.castShadow = o.material !== glass; } });
  return car;
}

export function initCar360(root, opt = {}) {
  injectCSS();
  root.innerHTML = '';
  root.classList.add('c360');

  const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
  renderer.shadowMap.enabled = !opt.model; // โมเดลจริงใช้เงาปลอมแทน เพื่อให้เบา
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;
  root.appendChild(renderer.domElement);
  const dom = renderer.domElement;

  const scene = new THREE.Scene();
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;

  const camera = new THREE.PerspectiveCamera(40, 1, 0.05, 100);

  const sun = new THREE.DirectionalLight(0xffffff, 1.8);
  sun.position.set(5, 8, 4);
  sun.castShadow = !opt.model;
  sun.shadow.mapSize.set(1024, 1024);
  Object.assign(sun.shadow.camera, { left: -6, right: 6, top: 6, bottom: -6 });
  scene.add(sun, new THREE.HemisphereLight(0x9fd8ee, 0x0a1220, 0.5));
  const cabinLight = new THREE.PointLight(0xffffff, 10, 8);
  cabinLight.position.set(0, 1.7, -0.3);
  cabinLight.visible = false;
  scene.add(cabinLight);

  // พื้น
  const floor = new THREE.Mesh(new THREE.CircleGeometry(9, 48).rotateX(-Math.PI / 2), new THREE.ShadowMaterial({ opacity: 0.4 }));
  floor.receiveShadow = true;
  scene.add(floor);
  const grid = new THREE.GridHelper(18, 36, 0x22d3ee, 0x0e4a5c);
  grid.material.transparent = true;
  grid.material.opacity = 0.22;
  grid.position.y = 0.001;
  scene.add(grid);
  const ring = new THREE.Mesh(new THREE.RingGeometry(3.4, 3.45, 96).rotateX(-Math.PI / 2), new THREE.MeshBasicMaterial({ color: 0x22d3ee, transparent: true, opacity: 0.6 }));
  ring.position.y = 0.005;
  scene.add(ring);
  if (opt.model) {
    // เงาปลอม (ไล่เฉดวงรี) แทนเงาจริงที่กินเครื่อง
    const cv = document.createElement('canvas'); cv.width = cv.height = 128;
    const cx = cv.getContext('2d'), gr = cx.createRadialGradient(64, 64, 4, 64, 64, 64);
    gr.addColorStop(0, 'rgba(0,0,0,.65)'); gr.addColorStop(1, 'rgba(0,0,0,0)');
    cx.fillStyle = gr; cx.fillRect(0, 0, 128, 128);
    const blob = new THREE.Mesh(new THREE.PlaneGeometry(7.5, 4.2).rotateX(-Math.PI / 2),
      new THREE.MeshBasicMaterial({ map: new THREE.CanvasTexture(cv), transparent: true, depthWrite: false }));
    blob.position.y = 0.01;
    scene.add(blob);
  }

  // รถ
  // พิกัดในโมเดล Fortuner (หน่วยเมตร พื้น y≈0.49 หน้ารถ +z พวงมาลัยขวา) — ใช้ data-eye="x,y,z" ปรับตำแหน่งตาคนขับได้
  const EYE_MODEL = new THREE.Vector3(-0.38, 1.78, -2.45);
  if (opt.eye) { const e = opt.eye.split(',').map(Number); if (e.length === 3 && e.every(isFinite)) EYE_MODEL.set(...e); }
  const eye = new THREE.Vector3(-0.45, 1.62, 0.18);
  let car = null;
  const paintHex = parseColor(opt.color);

  function note(text) {
    const n = document.createElement('div');
    n.className = 'c360-note';
    n.textContent = text;
    root.appendChild(n);
  }
  function useProcedural() {
    car = buildCar(paintHex ?? 0x888888);
    scene.add(car);
  }

  const loadEl = document.createElement('div');
  loadEl.className = 'c360-load';
  loadEl.innerHTML = '<span>กำลังโหลดโมเดล 3D… <span class="pct">0%</span></span><i><b></b></i>';

  if (opt.model) {
    root.appendChild(loadEl);
    const pct = loadEl.querySelector('.pct'), bar_ = loadEl.querySelector('b');
    const startLoad = () => new GLTFLoader().load(opt.model, gltf => {
      car = gltf.scene;
      // ปรับขนาดให้ยาว 4.8 ม. วางบนพื้น กึ่งกลางที่ 0,0
      const bb = new THREE.Box3().setFromObject(car);
      const size = bb.getSize(new THREE.Vector3());
      car.scale.setScalar(4.8 / Math.max(size.x, size.z));
      car.updateMatrixWorld(true);
      const bb2 = new THREE.Box3().setFromObject(car);
      const c = bb2.getCenter(new THREE.Vector3());
      car.position.set(-c.x, -bb2.min.y, -c.z);
      car.updateMatrixWorld(true);
      // สีตัวถัง (ถ้าไม่ใช่สีดำซึ่งเป็นสีเดิมของโมเดล)
      car.traverse(o => {
        if (!o.isMesh) return;
        const mats = Array.isArray(o.material) ? o.material : [o.material];
        mats.forEach(m => {
          if (m.name === 'Ext_CarPaint' && paintHex != null && paintHex !== 0x0b0b0e) {
            m.color.set(paintHex);
            m.metalness = 0.6;
          }
        });
      });
      const offset = car.position.clone();
      car = optimizeCar(car);
      scene.add(car);
      eye.copy(EYE_MODEL).add(offset);
      cabinLight.position.set(0, 1.95, -3.0).add(offset);
      cabinLight.intensity = 8;
      target.set(0, 0.9, 0);
      loadEl.style.opacity = 0;
      setTimeout(() => loadEl.remove(), 500);
    }, ev => {
      if (ev.total) { const p = Math.round(ev.loaded / ev.total * 100); pct.textContent = p + '%'; bar_.style.width = p + '%'; }
      else pct.textContent = (ev.loaded / 1048576).toFixed(0) + ' MB';
    }, err => {
      console.warn('โหลดโมเดล GLB ไม่สำเร็จ ใช้โมเดลสำรองแทน', err);
      loadEl.remove();
      useProcedural();
      note('โหลดโมเดลไม่สำเร็จ: ' + new URL(opt.model, location.href).href + ' (กด F12 ดูแท็บ Network/Console)');
    });
    // รอให้หน้าเว็บโหลดเสร็จก่อน ค่อยเริ่มโหลดโมเดล เพื่อไม่ให้หน้าค้าง
    const go = () => (window.requestIdleCallback ? requestIdleCallback(startLoad, { timeout: 1500 }) : setTimeout(startLoad, 300));
    if (document.readyState === 'complete') go(); else window.addEventListener('load', go, { once: true });
  } else {
    useProcedural();
    note('ไม่ได้ระบุไฟล์โมเดล (data-model ว่าง) จึงแสดงโมเดลสำรอง');
  }

  // UI
  const ui = document.createElement('div');
  ui.innerHTML = `
    <div class="c360-badge">360°</div>
    <div class="c360-hint"></div>
    <div class="c360-tabs"><button data-m="out" class="on">🚗 ภายนอก</button><button data-m="in">🪑 ภายใน</button></div>
    <button class="c360-fs" title="เต็มจอ">⛶</button>
    <div class="c360-fade"></div>`;
  while (ui.firstChild) root.appendChild(ui.firstChild);
  const hint = root.querySelector('.c360-hint');
  const fade = root.querySelector('.c360-fade');
  const tabs = [...root.querySelectorAll('.c360-tabs button')];
  const HINTS = { out: 'ลากเพื่อหมุนรอบคัน • เลื่อนล้อเมาส์/ถ่างนิ้วเพื่อซูม', in: 'ลากเพื่อมองรอบห้องโดยสาร 360° • เลื่อนล้อเพื่อซูม' };
  hint.textContent = HINTS.out;

  // ตัวควบคุมกล้อง
  let mode = 'out', theta = 0.6, phi = 1.2, radius = 8, yaw = 0, pitch = 0, fovIn = 75, lastAct = 0;
  const target = new THREE.Vector3(0, 0.9, 0);
  const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
  const pts = new Map();
  let pinch = 0;

  function rotate(dx, dy) {
    if (mode === 'out') { theta -= dx * 0.006; phi = clamp(phi - dy * 0.005, 0.2, 1.55); }
    else { yaw += dx * 0.005 * (fovIn / 75); pitch = clamp(pitch + dy * 0.005 * (fovIn / 75), -1.3, 1.3); }
  }
  function zoom(k) {
    if (mode === 'out') radius = clamp(radius * (1 + k * 0.5), 4.5, 12);
    else fovIn = clamp(fovIn * (1 + k * 0.5), 40, 95);
  }
  dom.addEventListener('pointerdown', e => {
    dom.setPointerCapture(e.pointerId);
    pts.set(e.pointerId, { x: e.clientX, y: e.clientY });
    lastAct = performance.now();
    root.classList.add('grab');
  });
  dom.addEventListener('pointermove', e => {
    const p = pts.get(e.pointerId);
    if (!p) return;
    const dx = e.clientX - p.x, dy = e.clientY - p.y;
    p.x = e.clientX; p.y = e.clientY;
    lastAct = performance.now();
    if (pts.size === 2) {
      const [a, b] = [...pts.values()];
      const d = Math.hypot(a.x - b.x, a.y - b.y);
      if (pinch) zoom((pinch - d) * 0.005);
      pinch = d;
      return;
    }
    pinch = 0;
    rotate(dx, dy);
  });
  const end = e => { pts.delete(e.pointerId); pinch = 0; if (!pts.size) root.classList.remove('grab'); };
  dom.addEventListener('pointerup', end);
  dom.addEventListener('pointercancel', end);
  dom.addEventListener('wheel', e => { e.preventDefault(); lastAct = performance.now(); zoom(e.deltaY * 0.001); }, { passive: false });

  function setMode(m) {
    if (m === mode) return;
    fade.classList.add('on');
    setTimeout(() => {
      mode = m;
      cabinLight.visible = m === 'in';
      hint.textContent = HINTS[m];
      tabs.forEach(b => b.classList.toggle('on', b.dataset.m === m));
      if (m === 'in') { yaw = 0; pitch = 0; }
      fade.classList.remove('on');
    }, 250);
  }
  tabs.forEach(b => b.addEventListener('click', e => { e.stopPropagation(); setMode(b.dataset.m); }));
  tabs.forEach(b => b.addEventListener('pointerdown', e => e.stopPropagation()));
  const fs = root.querySelector('.c360-fs');
  fs.addEventListener('pointerdown', e => e.stopPropagation());
  fs.addEventListener('click', () => {
    if (document.fullscreenElement) document.exitFullscreen();
    else root.requestFullscreen && root.requestFullscreen();
  });

  function resize() {
    const w = root.clientWidth || 600, h = root.clientHeight || 420;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }
  new ResizeObserver(resize).observe(root);
  resize();

  let visible = true;
  new IntersectionObserver(en => { visible = en[0].isIntersecting; }).observe(root);
  // ถ้าเครื่องช้า จะค่อยๆ ลดความละเอียดการวาดให้เอง
  let dpr = Math.min(window.devicePixelRatio || 1, 1.5), tAcc = 0, tN = 0, tLast = performance.now();
  renderer.setAnimationLoop(() => {
    if (!visible || document.hidden) { tLast = performance.now(); return; }
    const now = performance.now(), dt = now - tLast;
    tLast = now;
    if (dt < 250) {
      tAcc += dt; tN++;
      if (tN === 30) {
        if (tAcc / 30 > 24 && dpr > 0.6) { dpr = Math.max(0.6, dpr - 0.25); renderer.setPixelRatio(dpr); resize(); }
        tAcc = 0; tN = 0;
      }
    }
    if (mode === 'out') {
      if (!pts.size && performance.now() - lastAct > 2500) theta -= 0.004;
      camera.fov = 40;
      camera.position.set(
        target.x + radius * Math.sin(phi) * Math.sin(theta),
        target.y + radius * Math.cos(phi),
        target.z + radius * Math.sin(phi) * Math.cos(theta));
      camera.lookAt(target);
    } else {
      camera.fov = fovIn;
      camera.position.copy(eye);
      camera.rotation.set(pitch, Math.PI + yaw, 0, 'YXZ');
    }
    camera.updateProjectionMatrix();
    renderer.render(scene, camera);
  });

  return { setMode };
}

// เริ่มอัตโนมัติกับทุก element ที่มี data-car360
document.querySelectorAll('[data-car360]').forEach(el => {
  initCar360(el, { color: el.dataset.color, model: el.dataset.model, eye: el.dataset.eye });
});