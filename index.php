<?php
// ============================================================
//  CarVerse 3D | Combined 3D Experience & Homepage
//  index.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
 
// ดึงรถยนต์แนะนำ
$featured = $pdo->query("
    SELECT c.*, b.brand_name, t.type_name
    FROM   cars c
    JOIN   brands b    ON c.brand_id = b.brand_id
    JOIN   car_types t ON c.type_id  = t.type_id
    WHERE  c.status = 'available'
    ORDER  BY c.created_at DESC
    LIMIT  6
")->fetchAll();
 
// สถิติ
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM cars)   AS total_cars,
        (SELECT COUNT(*) FROM brands) AS total_brands,
        (SELECT COUNT(*) FROM users WHERE role='user') AS total_members,
        (SELECT COUNT(*) FROM test_drive_bookings) AS total_bookings
")->fetch();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>VANTA GT — The Art of Motion | CarVerse 3D</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;800&family=Barlow:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/home.css">
  <style>
    :root{--cy:#1fc8ff;--tx:#e9f7ff;--mu:#8aa3b2}
    *{box-sizing:border-box;margin:0}
    /* ปรับ body ให้มีความสูง 600vh เพื่อให้เกิดระยะ Scroll สำหรับควบคุม 3D Animation */
    body{background:#000;color:var(--tx);font:400 16px/1.5 Barlow,system-ui,sans-serif;height:600vh; overflow-x: hidden;}
    #c{position:fixed;inset:0;width:100%;height:100%;display:block; z-index: 1;}
   
    /* Navbar ทับด้านบนสุด */
    nav.main-custom-nav{position:fixed;top:14px;left:50%;transform:translateX(-50%);width:min(1100px,94vw);display:flex;align-items:center;justify-content:space-between;padding:10px 22px;border:1px solid rgba(31,200,255,.25);border-radius:999px;background:rgba(8,16,24,.45);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px);z-index:20}
    .logo{font:800 20px 'Barlow Condensed',sans-serif;letter-spacing:.14em; color: var(--tx); text-decoration: none;}
    .custom-nav-ul{display:flex;gap:28px;list-style:none; align-items: center;}
    .custom-nav-ul a{color:var(--mu);font-size:14px;cursor:pointer;text-decoration:none}
    .custom-nav-ul a:hover,.custom-nav-ul a:focus-visible{color:var(--cy);outline:0}
 
    .ov{position:fixed;left:5vw;top:50%;max-width:min(480px,90vw);opacity:0;pointer-events:none;z-index:2;transform:translateY(-50%)}
    .ov.r{left:auto;right:5vw;text-align:right}
    h1,h2{font:800 clamp(42px,8vw,110px)/.88 'Barlow Condensed',sans-serif;text-transform:uppercase}
    h2{font-size:clamp(36px,6vw,84px)}
    h1 b{color:var(--cy);font-weight:800}
    .ov p{margin-top:18px;color:var(--mu);max-width:32ch}
    .ov.r p{margin-left:auto}
    .btn-reveal{margin-top:26px;padding:14px 28px;border:0;border-radius:4px;background:var(--cy);color:#001018;font:800 15px 'Barlow Condensed',sans-serif;letter-spacing:.14em;cursor:pointer;pointer-events:auto;box-shadow:0 0 28px rgba(31,200,255,.5)}
    .btn-reveal:focus-visible{outline:2px solid #fff;outline-offset:3px}
    dl{display:grid;gap:16px;margin-top:22px}
    dt{font:800 clamp(34px,5vw,60px)/1 'Barlow Condensed',sans-serif;color:var(--cy)}
    dd{color:var(--mu);font-size:14px}
    #sw{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);z-index:4;display:flex;flex-direction:column;align-items:center;gap:10px;opacity:0;pointer-events:none}
    #sw div{display:flex;gap:14px}
    #sw button{width:40px;height:40px;border-radius:50%;border:2px solid rgba(255,255,255,.25);cursor:pointer}
    #sw button.on{border-color:var(--cy);box-shadow:0 0 14px var(--cy)}
    #sw button:focus-visible{outline:2px solid #fff;outline-offset:3px}
    #cn{font-size:14px;color:var(--mu)}
    .hs{position:fixed;left:0;top:0;width:22px;height:22px;margin:-11px 0 0 -11px;border-radius:50%;border:2px solid var(--cy);background:rgba(31,200,255,.3);box-shadow:0 0 16px var(--cy);opacity:0;pointer-events:none;z-index:3;cursor:pointer}
    .hs span{position:absolute;left:32px;top:-8px;white-space:nowrap;padding:8px 12px;background:rgba(4,10,18,.88);border:1px solid rgba(31,200,255,.45);border-radius:8px;font:500 13px Barlow,sans-serif;color:var(--tx);opacity:0;transition:opacity .2s;pointer-events:none}
    .hs:hover span,.hs:focus-visible span,.hs.open span{opacity:1}
    #ld{position:fixed;inset:0;background:#000;z-index:30;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;transition:opacity .8s}
    #ld .t{font:800 22px 'Barlow Condensed',sans-serif;letter-spacing:.3em}
    #bar{width:min(320px,70vw);height:3px;background:#12222c}
    #bar i{display:block;height:100%;width:0;background:var(--cy);box-shadow:0 0 12px var(--cy)}
    #ld small{color:var(--mu)}
    #pick{display:none;color:var(--mu)}
 
    /* ส่วนของเนื้อหาเว็บ PHP ด้านล่างที่จะเลื่อนมาทับเมื่อ scroll ผ่าน 3D ให้นำมาต่อด้านล่าง */
    .php-content-container {
      position: absolute;
      top: 600vh; /* อยู่ถัดจากระยะ Scroll 3D 6 หน้าจอ */
      left: 0;
      width: 100%;
      background: #050810;
      z-index: 25;
      padding-top: 50px;
    }
 
    @media(max-width:900px){.ov{left:5vw!important;right:5vw!important;max-width:100%!important;text-align:left!important}.ov.r p{margin-left:0}}
    @media(max-width:700px){.custom-nav-ul{gap:14px}.custom-nav-ul a{font-size:12px}}
  </style>
</head>
<body>
 
<canvas id="c"></canvas>
 
<!-- Navbar ผสานระหว่าง 3D และระบบ Login/Auth ของ PHP -->
<nav class="main-custom-nav">
  <a href="index.php" class="logo">VANTA GT <span style="font-size:0.6em; color:var(--cy)">3D</span></a>
  <ul class="custom-nav-ul">
    <li><a data-go="1">Exterior</a></li>
    <li><a data-go="3">Engine</a></li>
    <li><a data-go="4">Specs</a></li>
    <li><a data-go="5">Configure</a></li>
    <?php if (isLoggedIn()): ?>
      <li><a href="search.php">ค้นหารถ</a></li>
      <?php if (isAdmin()): ?>
        <li><a href="add_car.php">เพิ่มรถ</a></li>
      <?php endif; ?>
      <li><a href="logout.php" style="color:#ff5a0a;">ออกจากระบบ</a></li>
    <?php else: ?>
      <li><a href="login.php">เข้าสู่ระบบ</a></li>
    <?php endif; ?>
  </ul>
</nav>
 
<!-- Text Overlays สำหรับ 3D Experience -->
<div class="ov" data-i="0">
  <h1>The art<br>of <b>motion.</b></h1>
  <p>A machine sculpted by wind and intent. Scroll to begin a launch experience unlike any other.</p>
  <button class="btn-reveal" data-go="1">BEGIN THE REVEAL</button>
</div>
<div class="ov r" data-i="1">
  <h2>Every detail considered.</h2>
  <p>Not a single line is accidental. The form rotates to reveal a silhouette honed in the wind tunnel.</p>
</div>
<div class="ov" data-i="2">
  <h2>Built around the driver.</h2>
  <p>Carbon, leather and a cockpit that puts every control within reach.</p>
</div>
<div class="ov" data-i="3" style="top:auto;bottom:12vh;transform:none">
  <h2>Unleashed.</h2>
  <p>Hover the markers to explore the powertrain.</p>
</div>
<div class="ov r" data-i="4">
  <h2>By the numbers.</h2>
  <dl>
    <div><dt>2.9s</dt><dd>0–100 km/h</dd></div>
    <div><dt>903 hp</dt><dd>V8 twin-turbo with electric assist</dd></div>
    <div><dt>350 km/h</dt><dd>Top speed</dd></div>
  </dl>
</div>
<div class="ov" data-i="5" style="top:26%;transform:none">
  <h2>Make it yours.</h2>
  <p>Choose a finish. The model updates live.</p>
</div>
 
<div id="sw"><span id="cn"></span><div id="sws"></div></div>
 
<!-- Loading Screen -->
<div id="ld">
  <div class="t">VANTA GT</div>
  <div id="bar"><i></i></div>
  <small id="pct">0%</small>
  <label id="pick">Could not load the model automatically. Choose the .glb file: <input type="file" id="file" accept=".glb,.gltf"></label>
</div>
 
<!-- ========================================================== -->
<!-- ส่วนเนื้อหาเว็บไซต์ PHP (แสดงรถแนะนำ, สถิติ และ Footer ด้านล่าง) -->
<!-- ========================================================== -->
<div class="php-content-container">
 
  <!-- FEATURED CARS SECTION -->
  <section class="section" style="padding:80px 0;">
    <div class="container">
      <div style="text-align:center; margin-bottom: 40px;">
        <div class="section-title" style="font-size: 2.5rem; font-weight:800;">รถยนต์<span> แนะนำในระบบ</span></div>
        <div class="section-divider" style="margin: 10px auto;"></div>
        <p class="section-subtitle" style="color:var(--mu);">คัดสรรรถยนต์พรีเมียมที่ดีที่สุด พร้อมเชื่อมต่อระบบฐานข้อมูลจริง</p>
      </div>
 
      <div class="car-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:24px; max-width:1200px; margin:0 auto; padding:0 20px;">
        <?php foreach ($featured as $car): ?>
        <div class="car-card" style="background:rgba(12,22,34,0.6); border:1px solid rgba(31,200,255,0.2); border-radius:12px; overflow:hidden;">
          <div style="overflow:hidden; height:200px; background:#000; position:relative;">
            <?php if (!empty($car['image_url'])): ?>
              <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['model_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--mu);">ไม่มีรูปภาพ</div>
            <?php endif; ?>
          </div>
          <div style="padding:20px;">
            <div style="font-size:0.85rem; color:var(--cy); margin-bottom:6px;"><?= htmlspecialchars($car['brand_name']) ?> · <?= htmlspecialchars($car['type_name']) ?></div>
            <div style="font-size:1.2rem; font-weight:700; color:var(--tx); margin-bottom:10px;"><?= htmlspecialchars($car['model_name']) ?></div>
            <div style="font-size:1.1rem; font-weight:600; color:#fff; margin-bottom:16px;"><?= number_format($car['price']) ?> บาท</div>
            <a href="search.php?id=<?= $car['car_id'] ?>" class="btn-reveal" style="display:block; text-align:center; text-decoration:none; margin-top:0; padding:10px;">ดูรายละเอียด</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
 
      <div style="text-align:center; margin-top:50px;">
        <a href="search.php" class="btn-reveal" style="display:inline-block; text-decoration:none;">ดูรถทั้งหมดในระบบ →</a>
      </div>
    </div>
  </section>
 
  <!-- FOOTER -->
  <footer class="footer" style="background:#020408; padding:40px 0; border-top:1px solid rgba(31,200,255,0.1); text-align:center; color:var(--mu);">
    <div class="container">
      <p>© 2026 CarVerse 3D & VANTA GT. All rights reserved.</p>
    </div>
  </footer>
 
</div>
 
<!-- Three.js & Application Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/objects/Reflector.js"></script>
<script>
// ================== CONFIG ==================
const CONFIG = {
  modelUrl: 'assets/models/mclaren_p1.glb', 
  // หากยังติด 404 จาก Server XAMPP แนะนำให้เปลี่ยนชื่อไฟล์ในโฟลเดอร์ models เป็น mclaren_p1.txt หรืออัพโหลดผ่านปุ่มเลือกไฟล์ด้านล่างแทนชั่วคราว
  sizeHint: 19169040,           
  length: 5.2,                 
  paintRegex: /carpaint/i,     
  colors: [['Vanta Black','#0b0b0e'],['Cyan','#0b86d6'],['Signal Red','#c1121f'],['Titanium','#8a9099'],['Volcano','#ff5a0a'],['Ice White','#e8edf2']],
  hotspots: [
    {u:-.35,v:.55,w:0,t:'V8 Twin-Turbo Engine · 903 hp'},
    {u:.05,v:.8,w:.35,t:'0–100 km/h in 2.9s'},
    {u:.62,v:.3,w:.95,t:'Carbon-ceramic brakes'},
    {u:.9,v:.28,w:0,t:'Active front aero'}
  ]
};
// ===========================================
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
const clamp=(x,a=0,b=1)=>Math.min(b,Math.max(a,x)),ss=x=>(x=clamp(x))*x*(3-2*x),mix=(a,b,t)=>a+(b-a)*t;
const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
const renderer=new THREE.WebGLRenderer({canvas:$('#c'),antialias:true});
renderer.setPixelRatio(Math.min(devicePixelRatio,1.75));
renderer.outputEncoding = THREE.sRGBEncoding;
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 0.95;
 
const scene=new THREE.Scene();
scene.background=new THREE.Color(0x000000);
scene.fog=new THREE.FogExp2(0x000000, 0.045);
 
const camera=new THREE.PerspectiveCamera(38,1,.05,80);
 
{const es=new THREE.Scene();es.background=new THREE.Color(0x010206);
 const sb=(w,h,x,y,z,c,i)=>{const m=new THREE.Mesh(new THREE.PlaneGeometry(w,h),new THREE.MeshBasicMaterial({color:new THREE.Color(c).multiplyScalar(i),side:THREE.DoubleSide}));m.position.set(x,y,z);m.lookAt(0,0,0);es.add(m)};
 sb(14,6,0,10,0,0xffffff,8);sb(3,8,-9,4,0,0x33bbff,10);sb(3,8,9,4,0,0x33bbff,10);sb(12,2,0,2,-10,0xffffff,6);
 scene.environment=new THREE.PMREMGenerator(renderer).fromScene(es,.03).texture;}
 
const sp=new THREE.SpotLight(0xd9f2ff, 210, 32, 0.45, 0.85, 1.5);
sp.position.set(5.5, 13, 3);
sp.castShadow = true;
scene.add(sp, sp.target);
 
scene.add(new THREE.AmbientLight(0x02050a, 0.3));
[[6, 2, -2], [-6, 2, -2]].forEach(p=>{const l=new THREE.PointLight(0x0088cc, 15, 16, 2);l.position.set(...p);scene.add(l)});
 
const cone=new THREE.Mesh(new THREE.CylinderGeometry(.1,3.5,10,48,1,true),new THREE.ShaderMaterial({transparent:true,depthWrite:false,blending:THREE.AdditiveBlending,side:THREE.DoubleSide,uniforms:{c:{value:new THREE.Color(0x3399ff)}},
 vertexShader:'varying vec2 v;void main(){v=uv;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);}',
 fragmentShader:'varying vec2 v;uniform vec3 c;void main(){float a=pow(v.y,2.0)*.08;gl_FragColor=vec4(c*a,a);}'}));
cone.position.set(5.5,5,1);cone.rotation.x=0.15;scene.add(cone);
 
// แก้ไขโค้ด Reflector ตรงนี้ไม่ให้บรรทัดขาดตอน
const floor = new THREE.Reflector(new THREE.PlaneGeometry(60, 60), {
  color: 0x040406,
  textureWidth: innerWidth * renderer.getPixelRatio(),
  textureHeight: innerHeight * renderer.getPixelRatio(),
  clipBias: .003
});
floor.rotation.x=-Math.PI/2;scene.add(floor);
 
const grid=new THREE.GridHelper(60, 100, 0x003355, 0x010408);
grid.position.y=.004;
grid.material.transparent=true;
grid.material.opacity=0.08;
scene.add(grid);
 
{const cv=document.createElement('canvas');cv.width=cv.height=512;const g=cv.getContext('2d'),r=g.createRadialGradient(256,256,0,256,256,256);
 r.addColorStop(0,'rgba(0,0,0,0.85)');r.addColorStop(.4,'rgba(0,0,0,.6)');r.addColorStop(1,'rgba(0,0,0,1)');g.fillStyle=r;g.fillRect(0,0,512,512);
 const o=new THREE.Mesh(new THREE.PlaneGeometry(60,60),new THREE.MeshBasicMaterial({map:new THREE.CanvasTexture(cv),transparent:true,depthWrite:false}));
 o.rotation.x=-Math.PI/2;o.position.y=.01;scene.add(o);}
 
const car=new THREE.Group();
car.position.x = 5.5;
scene.add(car);
 
const door=[],hood=[],paint=[],paintOrig=[];let hl=[],eng,size=new THREE.Vector3(1,1,1),ready=false;
const ph=new THREE.Mesh(new THREE.BoxGeometry(2,.9,4.6),new THREE.MeshBasicMaterial({color:0x1fc8ff,wireframe:true,transparent:true,opacity:.25}));ph.position.y=.5;car.add(ph);
 
function build(gltf){
  const model=gltf.scene;model.updateMatrixWorld(true);
  const box=new THREE.Box3().setFromObject(model),bs=box.getSize(new THREE.Vector3()),bc=box.getCenter(new THREE.Vector3());
  const ax=bs.x>bs.z?'x':'z';let sign=1;
  const hb=new THREE.Box3();model.traverse(o=>{if(o.name.startsWith('GEO_HOOD'))hb.expandByObject(o)});
  if(!hb.isEmpty())sign=Math.sign(hb.getCenter(new THREE.Vector3())[ax]-bc[ax])||1;
  const f=new THREE.Vector3();f[ax]=sign;model.rotation.y=-Math.atan2(f.x,f.z);model.updateMatrixWorld(true);
  let b2=new THREE.Box3().setFromObject(model);model.scale.setScalar(CONFIG.length/b2.getSize(new THREE.Vector3()).z);model.updateMatrixWorld(true);
  b2=new THREE.Box3().setFromObject(model);const c2=b2.getCenter(new THREE.Vector3());
  model.position.set(-c2.x,-b2.min.y,-c2.z);car.remove(ph);car.add(model);car.updateMatrixWorld(true);
  size=new THREE.Box3().setFromObject(car).getSize(new THREE.Vector3());
 
  const seen=new Set();
  model.traverse(o=>{if(!o.isMesh)return;(Array.isArray(o.material)?o.material:[o.material]).forEach(m=>{if(seen.has(m))return;seen.add(m);
    if(CONFIG.paintRegex.test(m.name)){
      paint.push(m);paintOrig.push({c:m.color.clone(),map:m.map});
      m.color.setHex(0x050507);m.metalness=0.92;m.roughness=0.16;
      if('clearcoat' in m){m.clearcoat=1.0;m.clearcoatRoughness=0.08;}
    }
    if(/headlight/i.test(m.name)){m.emissive.setHex(0x3cc8ff);m.emissiveIntensity=4;hl.push(m)}});});
 
  const grp=p=>{const a=[];model.traverse(o=>{if(o.name.startsWith(p)&&!(o.parent&&o.parent.name.startsWith(p)))a.push(o)});return a};
  const pivot=(nodes,hx,hy,hz)=>{const g=new THREE.Group();g.position.set(hx,hy,hz);car.add(g);g.updateMatrixWorld(true);nodes.forEach(n=>g.attach(n));return g};
  const sides={};grp('GEO_DOOR').forEach(n=>{const b=new THREE.Box3().setFromObject(n);if(b.isEmpty())return;const s=b.getCenter(new THREE.Vector3()).x<0?-1:1;(sides[s]=sides[s]||[]).push([n,b])});
  for(const s in sides){const B=new THREE.Box3();sides[s].forEach(([n,b])=>B.union(b));
    const g=pivot(sides[s].map(x=>x[0]),s>0?B.min.x:B.max.x,B.max.y,(B.min.z+B.max.z)/2);door.push({g,s:+s,x:g.position.x})}
  const hn=grp('GEO_HOOD');if(hn.length){const B=new THREE.Box3();hn.forEach(n=>B.expandByObject(n));hood.push(pivot(hn,(B.min.x+B.max.x)/2,B.max.y,B.min.z))}
 
  eng=new THREE.Group();eng.position.set(0,size.y*.5,-size.z*.17);eng.renderOrder=5;car.add(eng);
  const lm=new THREE.LineBasicMaterial({color:0x2fd0ff,transparent:true,depthTest:false,blending:THREE.AdditiveBlending}),fm=new THREE.MeshBasicMaterial({color:0x0a9cff,transparent:true,depthTest:false,depthWrite:false,blending:THREE.AdditiveBlending});
  eng.userData={lm,fm};
  const add=(g,x,y,z,rx=0,rz=0)=>{const l=new THREE.LineSegments(new THREE.EdgesGeometry(g,25),lm),m=new THREE.Mesh(g,fm);[l,m].forEach(o=>{o.position.set(x,y,z);o.rotation.set(rx,0,rz);o.renderOrder=5;eng.add(o)})};
  for(let i=0;i<4;i++)[-1,1].forEach(s=>{const z=(i-1.5)*.21;add(new THREE.CylinderGeometry(.085,.085,.32,12),s*.12,.1,z,0,-s*.4);add(new THREE.BoxGeometry(.2,.1,.17),s*.27,.25,z,0,-s*.4)});
  add(new THREE.BoxGeometry(.3,.12,.9),0,.32,0);add(new THREE.CylinderGeometry(.04,.04,1.1,10),0,-.12,0,Math.PI/2);
  [-1,1].forEach(s=>add(new THREE.CylinderGeometry(.11,.11,.2,14),s*.42,.05,-.55,0,Math.PI/2));
  add(new THREE.BoxGeometry(.5,.3,.45),0,-.02,-.85);add(new THREE.CylinderGeometry(.14,.14,.18,16),0,-.02,.62,Math.PI/2);
 
  CONFIG.hotspots.forEach(h=>{const b=document.createElement('button');b.className='hs';b.setAttribute('aria-label',h.t);b.innerHTML='<span>'+h.t+'</span>';b.onclick=()=>b.classList.toggle('open');document.body.appendChild(b);
    h.el=b;h.p=new THREE.Vector3(h.w*size.x/2,h.v*size.y,h.u*size.z/2)});
  ready=true;
}
 
const bar=$('#bar i'),pct=$('#pct');
function load(url){new THREE.GLTFLoader().load(url,g=>{build(g);const l=$('#ld');l.style.opacity=0;setTimeout(()=>l.remove(),900)},
  x=>{const p=clamp(x.lengthComputable?x.loaded/x.total:x.loaded/CONFIG.sizeHint);bar.style.width=p*100+'%';pct.textContent=Math.round(p*100)+'%'},
  e=>{console.error(e);$('#pick').style.display='block'})}
$('#file').onchange=e=>{const f=e.target.files[0];if(f){$('#pick').style.display='none';load(URL.createObjectURL(f))}};
load(CONFIG.modelUrl);
 
let target=null;
CONFIG.colors.forEach(([n,h],i)=>{const b=document.createElement('button');b.style.background=h;b.title=n;b.setAttribute('aria-label',n);
  b.onclick=()=>{$$('#sws button').forEach(x=>x.classList.remove('on'));b.classList.add('on');$('#cn').textContent=n;target=new THREE.Color(h);
    paint.forEach(m=>{m.map=null;m.needsUpdate=true;m.metalness=.92;m.roughness=.16})};$('#sws').appendChild(b)});
 
const KF=[
 {p:[2.2, 1.2, 6.8], l:[5.5, .45, 0], y: -1.35, f: 38},
 {p:[0.2, 1.2, 7.0], l:[5.5, .5, 0], y: -.4, f: 40},
 {p:[5.88, 1.0, -.05], l:[5.88, .82, 2.5], y: 0.2, f: 64, i: 1},
 {p:[10.1, 2.7, -4.6], l:[5.5, .5, -.6], y: 0.2, f: 40},
 {p:[10.7, 1.1, 4.4], l:[5.5, .5, 0], y: 0.2, f: 40},
 {p:[5.5, 1.6, 8.5], l:[5.5, .5, 0], y: .5, f: 38}];
 
let S=0,mx=0,my=0;const look=new THREE.Vector3(),pos=new THREE.Vector3(),clock=new THREE.Clock();
addEventListener('pointermove',e=>{mx=e.clientX/innerWidth-.5;my=e.clientY/innerHeight-.5});
$$('[data-go]').forEach(a=>a.onclick=()=>scrollTo({top:a.dataset.go*innerHeight,behavior:'smooth'}));
function resize(){renderer.setSize(innerWidth,innerHeight,false);camera.aspect=innerWidth/innerHeight;camera.updateProjectionMatrix()}
addEventListener('resize',resize);resize();
const v3=new THREE.Vector3();
function frame(){
  const dt=Math.min(clock.getDelta(),.05),t=clock.elapsedTime;
  S+=(scrollY/innerHeight-S)*(reduce?1:1-Math.exp(-dt*4));
  const i=Math.min(5,Math.floor(S)),j=Math.min(5,i+1),f=i>=5?0:ss(S-i),A=KF[i],B=KF[j];
  const m=(a,b)=>a.map((x,k)=>mix(x,b[k],f));
  const inside=mix(A.i||0,B.i||0,f),k=1+((innerWidth<innerHeight?1.6:1)-1)*(1-inside);
  look.fromArray(m(A.l,B.l));pos.fromArray(m(A.p,B.p));pos.sub(look).multiplyScalar(k).add(look);
  pos.x+=mx*.4*(1-inside);pos.y-=my*.15*(1-inside);
  camera.position.lerp(pos,1);camera.lookAt(look);camera.fov=mix(A.f,B.f,f);camera.updateProjectionMatrix();
  car.rotation.y=mix(A.y,B.y,f)+Math.PI*2*ss(S/1.9);
  cone.material.uniforms.c.value.setHex(0x3399ff);
 
  $$('.ov').forEach(o=>{const d=S-o.dataset.i,a=clamp(1-Math.abs(d)*2.2);o.style.opacity=a;});
  const sw=$('#sw'),sa=clamp((S-4.4)*2);sw.style.opacity=sa;sw.style.pointerEvents=sa>.5?'auto':'none';
 
  const e=ss((S-2.6)/.8)*(1-ss((S-3.8)/.7));
  if(ready){
    door.forEach(d=>{d.g.rotation.z=d.s*e*.95;d.g.position.x=d.x+d.s*e*.15});
    hood.forEach(h=>h.rotation.x=-e*.85);
    paint.forEach(p=>{p.transparent=e>.01;p.opacity=1-.82*e;p.depthWrite=e<.1;p.emissiveIntensity=e*.7;if(target)p.color.lerp(target,.12)});
    eng.visible=e>.01;eng.userData.lm.opacity=e*(.75+.25*Math.sin(t*3));eng.userData.fm.opacity=e*.1;
    CONFIG.hotspots.forEach(h=>{v3.copy(h.p);car.localToWorld(v3);v3.project(camera);const on=e>.6&&v3.z<1;
     h.el.style.opacity=on?1:0;h.el.style.pointerEvents=on?'auto':'none';h.el.style.transform='translate('+(v3.x*.5+.5)*innerWidth+'px,'+(-v3.y*.5+.5)*innerHeight+'px)'});
  }
  renderer.render(scene,camera);requestAnimationFrame(frame);
}
frame();
</script>
</body>
</html>