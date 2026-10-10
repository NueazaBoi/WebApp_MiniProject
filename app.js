// Apex Hypermotors VIP Portal & Atelier Engine
// Comprehensive Client-Side Application

// 1. CARS INVENTORY DATABASE (All 14 marques from assignment requirements)
const CARS_DATABASE = [
  {
    id: "ferrari-sf90",
    name: "Ferrari SF90 Stradale Assetto Fiorano",
    brand: "Ferraries",
    price: 3850000,
    topSpeed: "340 km/h",
    topSpeedNum: 340,
    acceleration: "2.5s (0-100)",
    horsepower: "986 HP",
    hpNum: 986,
    transmission: "8-Speed Dual Clutch",
    engine: "4.0L Twin-Turbo V8 Hybrid",
    image: "https://images.unsplash.com/photo-1592198084033-aade902d1aae?auto=format&fit=crop&w=1200&q=80",
    badge: "Hypercar Flagship",
    description: "The pinnacle of Maranello engineering. Tri-motor plug-in hybrid architecture delivering blistering lap times and pure Italian emotion.",
    colors: [
      { name: "Rosso Corsa Red", hex: "#d40000" },
      { name: "Giallo Modena Yellow", hex: "#ffd700" },
      { name: "Nero Daytona Black", hex: "#111111" },
      { name: "Bianco Avus White", hex: "#f0f0f0" }
    ]
  },
  {
    id: "mercedes-amg-one",
    name: "Mercedes-AMG ONE Hypercar",
    brand: "Mercedes benz",
    price: 3200000,
    topSpeed: "352 km/h",
    topSpeedNum: 352,
    acceleration: "2.7s (0-100)",
    horsepower: "1049 HP",
    hpNum: 1049,
    transmission: "7-Speed Automated Manual",
    engine: "1.6L Turbocharged V6 Formula 1 Hybrid",
    image: "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=80",
    badge: "F1 Technology",
    description: "Real Formula 1 championship powertrain brought straight to the open highway. Carbon monocoque with active aerodynamic flaps.",
    colors: [
      { name: "Petronas Silver & Teal", hex: "#95a5a6" },
      { name: "Obsidian Metallic Black", hex: "#1a1a1a" },
      { name: "Selenite Magno Grey", hex: "#4b4f56" }
    ]
  },
  {
    id: "ford-gt-carbon",
    name: "Ford GT Liquid Carbon Heritage",
    brand: "Ford",
    price: 2150000,
    topSpeed: "347 km/h",
    topSpeedNum: 347,
    acceleration: "3.0s (0-100)",
    horsepower: "660 HP",
    hpNum: 660,
    transmission: "7-Speed Dual Clutch",
    engine: "3.5L Twin-Turbo EcoBoost V6",
    image: "https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80",
    badge: "Le Mans Champion",
    description: "Crafted entirely with exposed carbon fiber bodywork and lightweight titanium exhaust system born from 24 Hours of Le Mans victory.",
    colors: [
      { name: "Liquid Raw Carbon", hex: "#2b2b2b" },
      { name: "Gulf Racing Blue", hex: "#4ba3e3" },
      { name: "Frozen White", hex: "#ffffff" }
    ]
  },
  {
    id: "byd-yangwang-u9",
    name: "BYD Yangwang U9 DiSus-X",
    brand: "BYD",
    price: 1650000,
    topSpeed: "375 km/h",
    topSpeedNum: 375,
    acceleration: "2.36s (0-100)",
    horsepower: "1287 HP",
    hpNum: 1287,
    transmission: "Single-Speed Quad Motor",
    engine: "Quad Electric Motors (e4 Platform)",
    image: "https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80",
    badge: "Electric Hypercar",
    description: "Next-generation electric hypercar equipped with revolutionary DiSus-X intelligent body control system and quad motor torque vectoring.",
    colors: [
      { name: "Galactic Vermilion Red", hex: "#c0392b" },
      { name: "Neon Cyber Silver", hex: "#bdc3c7" },
      { name: "Midnight Nebula Purple", hex: "#4a235a" }
    ]
  },
  {
    id: "tesla-roadster",
    name: "Tesla Roadster SpaceX Edition",
    brand: "Tesla",
    price: 1500000,
    topSpeed: "400+ km/h",
    topSpeedNum: 400,
    acceleration: "1.9s (0-100)",
    horsepower: "1350 HP",
    hpNum: 1350,
    transmission: "All-Wheel Drive Electric",
    engine: "Tri-Motor AWD with Cold Gas Thrusters",
    image: "https://images.unsplash.com/photo-1560958089-b8a1929cea89?auto=format&fit=crop&w=1200&q=80",
    badge: "Fastest Electric",
    description: "The quickest accelerating supercar on Earth with SpaceX cold-gas thruster package, panoramic glass roof and unmatched 1000 km range.",
    colors: [
      { name: "Deep Crimson Red", hex: "#8e001c" },
      { name: "Stealth Satin Black", hex: "#1f1f1f" },
      { name: "Pearl White Multi-Coat", hex: "#f9f9f9" }
    ]
  },
  {
    id: "audi-r8-gt",
    name: "Audi R8 V10 GT RWD Limited",
    brand: "Audi",
    price: 1250000,
    topSpeed: "320 km/h",
    topSpeedNum: 320,
    acceleration: "3.4s (0-100)",
    horsepower: "612 HP",
    hpNum: 612,
    transmission: "7-Speed S Tronic",
    engine: "5.2L Naturally Aspirated V10",
    image: "https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?auto=format&fit=crop&w=1200&q=80",
    badge: "Naturally Aspirated V10",
    description: "The farewell edition of the legendary mid-engine German supercar. Pure rear-wheel drive thrills with motorsport aerodynamics.",
    colors: [
      { name: "Suzuka Grey Matte", hex: "#7f8c8d" },
      { name: "Mythos Black Metallic", hex: "#121212" },
      { name: "Tango Red Metallic", hex: "#b03a2e" }
    ]
  },
  {
    id: "honda-nsx-type-s",
    name: "Honda NSX Type S Twin-Turbo",
    brand: "Honda",
    price: 980000,
    topSpeed: "308 km/h",
    topSpeedNum: 308,
    acceleration: "2.9s (0-100)",
    horsepower: "600 HP",
    hpNum: 600,
    transmission: "9-Speed Dual Clutch",
    engine: "3.5L Twin-Turbo V6 Hybrid SH-AWD",
    image: "https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&w=1200&q=80",
    badge: "Super Hybrid",
    description: "Limited to 350 units worldwide. Carbon fiber roof, Sport Hybrid Super Handling All-Wheel Drive and lightning throttle response.",
    colors: [
      { name: "Gotham Gray Matte", hex: "#566573" },
      { name: "Thermal Orange Pearl", hex: "#e67e22" },
      { name: "Indy Yellow Pearl", hex: "#f4d03f" }
    ]
  },
  {
    id: "bmw-m8-competition",
    name: "BMW M8 Competition Gran Coupe",
    brand: "BMW",
    price: 890000,
    topSpeed: "305 km/h",
    topSpeedNum: 305,
    acceleration: "3.2s (0-100)",
    horsepower: "617 HP",
    hpNum: 617,
    transmission: "8-Speed M Steptronic",
    engine: "4.4L M TwinPower Turbo V8",
    image: "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=80",
    badge: "Luxury Grand Tourer",
    description: "Luxury meets explosive dynamics. M xDrive with switchable 2WD drift mode, carbon-ceramic brakes and bespoke executive interior.",
    colors: [
      { name: "Isle of Man Green", hex: "#1e8449" },
      { name: "Marina Bay Blue", hex: "#2980b9" },
      { name: "Alpine White", hex: "#ffffff" }
    ]
  },
  {
    id: "nissan-gtr-nismo",
    name: "Nissan GT-R NISMO Special Edition",
    brand: "Nissan",
    price: 820000,
    topSpeed: "330 km/h",
    topSpeedNum: 330,
    acceleration: "2.8s (0-100)",
    horsepower: "600 HP",
    hpNum: 600,
    transmission: "6-Speed Dual Clutch",
    engine: "3.8L Twin-Turbo VR38DETT V6",
    image: "https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=1200&q=80",
    badge: "Godzilla Track Beast",
    description: "The ultimate Japanese icon. GT3 race-spec turbochargers, carbon fiber hood, roof, and Brembo carbon-ceramic matrix braking.",
    colors: [
      { name: "NISMO Stealth Gray", hex: "#626567" },
      { name: "Solid White Pearl", hex: "#f2f3f4" },
      { name: "Ultimate Metal Silver", hex: "#95a5a6" }
    ]
  },
  {
    id: "mazda-rx-vision",
    name: "Mazda RX-Vision Rotary Concept",
    brand: "Mazda",
    price: 650000,
    topSpeed: "315 km/h",
    topSpeedNum: 315,
    acceleration: "3.5s (0-100)",
    horsepower: "520 HP",
    hpNum: 520,
    transmission: "6-Speed Sequential",
    engine: "Skyactiv-R Quad-Rotor Rotary",
    image: "https://images.unsplash.com/photo-1541348263662-e0c8de4259ba?auto=format&fit=crop&w=1200&q=80",
    badge: "Rotary Legend",
    description: "KODO soul in motion design celebrating the timeless rotary heritage. Screaming 9,500 RPM redline and immaculate balance.",
    colors: [
      { name: "Soul Red Crystal", hex: "#900c3f" },
      { name: "Machine Grey Metallic", hex: "#2c3e50" },
      { name: "Snowflake White Pearl", hex: "#ecf0f1" }
    ]
  },
  {
    id: "toyota-gr-supra-gt4",
    name: "Toyota GR Supra GT4 100 Edition",
    brand: "Toyota",
    price: 490000,
    topSpeed: "300 km/h",
    topSpeedNum: 300,
    acceleration: "3.7s (0-100)",
    horsepower: "450 HP",
    hpNum: 450,
    transmission: "7-Speed Sport Automatic",
    engine: "3.0L Inline-6 Twin-Scroll Turbo",
    image: "https://images.unsplash.com/photo-1616422285623-13ff0162193c?auto=format&fit=crop&w=1200&q=80",
    badge: "Gazoo Racing",
    description: "Collector-grade track weapon engineered by TOYOTA GAZOO Racing. Carbon aero front splitter and Akrapovic exhaust.",
    colors: [
      { name: "Plasma Orange Special", hex: "#d35400" },
      { name: "Matte Storm Grey", hex: "#424949" },
      { name: "Prominence Red", hex: "#c0392b" }
    ]
  },
  {
    id: "subaru-wrx-sti-s209",
    name: "Subaru WRX STI S209 Hyper Blue",
    brand: "Subaru",
    price: 360000,
    topSpeed: "280 km/h",
    topSpeedNum: 280,
    acceleration: "4.1s (0-100)",
    horsepower: "341 HP",
    hpNum: 341,
    transmission: "6-Speed Close-Ratio Manual",
    engine: "2.5L Turbocharged Boxer-4 EJ25",
    image: "https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=1200&q=80",
    badge: "Rally Heritage",
    description: "STI bespoke widebody limited series with forged BBS wheels, Brembo 6-piston monoblocks and symmetrical AWD.",
    colors: [
      { name: "World Rally Blue Pearl", hex: "#0b5394" },
      { name: "Crystal White Pearl", hex: "#f7f9f9" },
      { name: "Magnetite Grey", hex: "#3b3f46" }
    ]
  },
  {
    id: "suzuki-vision-gt",
    name: "Suzuki Vision GT Supercar",
    brand: "Suzuki",
    price: 280000,
    topSpeed: "295 km/h",
    topSpeedNum: 295,
    acceleration: "3.6s (0-100)",
    horsepower: "432 HP",
    hpNum: 432,
    transmission: "6-Speed Paddle Sequential",
    engine: "1.3L Hayabusa Inline-4 + Triple Electric",
    image: "https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80",
    badge: "Hayabusa Hybrid",
    description: "Combining legendary Suzuki Hayabusa motorcycle engine with tri-motor hybrid drive in an ultralight carbon roadster chassis.",
    colors: [
      { name: "Champion Yellow #4", hex: "#f1c40f" },
      { name: "Speedy Blue Metallic", hex: "#1f618d" },
      { name: "Super Black Pearl", hex: "#17202a" }
    ]
  },
  {
    id: "isuzu-arctic-420r",
    name: "Isuzu D-Max Arctic 420R Hyper-Truck",
    brand: "Izusu",
    price: 240000,
    topSpeed: "250 km/h",
    topSpeedNum: 250,
    acceleration: "4.8s (0-100)",
    horsepower: "420 HP",
    hpNum: 420,
    transmission: "8-Speed Heavy Duty Sport Auto",
    engine: "3.0L Bi-Turbo Twin-Charged Diesel-Electric",
    image: "https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=80",
    badge: "Hyper Off-Roader",
    description: "Bespoke trophy edition super-truck with Fox Racing bypass dampers, flared carbon fenders, aggressive aero snorkel, and 420 HP.",
    colors: [
      { name: "Valencia Orange Matte", hex: "#d35400" },
      { name: "Basalt Black Mica", hex: "#212f3d" },
      { name: "Desert Sand Storm", hex: "#d4ac0d" }
    ]
  }
];

const DEFAULT_WALLET_AMOUNT = 999999999999999; // $999 Trillion

// Format Currency
function formatCurrency(amount) {
  return '$' + Number(amount).toLocaleString('en-US');
}

// Global Toast Notification
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `apex-toast ${type}`;

  const iconClass = type === 'success' ? 'fa-check-circle text-success' :
                    type === 'error'   ? 'fa-circle-exclamation text-crimson' :
                    'fa-bolt text-gold';

  toast.innerHTML = `
    <i class="fas ${iconClass} fa-lg"></i>
    <span style="flex:1;">${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'toastSlideOut 0.35s forwards';
    setTimeout(() => toast.remove(), 350);
  }, 3500);
}

// Web Audio API Supercar Rev Synthesis
function playEngineRevAudio() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    const ctx = new AudioContext();

    // Low rumble oscillator
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    const filter = ctx.createBiquadFilter();

    osc.type = 'sawtooth';
    filter.type = 'lowpass';
    filter.frequency.setValueAtTime(120, ctx.currentTime);
    filter.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.4);
    filter.frequency.exponentialRampToValueAtTime(150, ctx.currentTime + 1.2);

    osc.frequency.setValueAtTime(45, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(220, ctx.currentTime + 0.4);
    osc.frequency.exponentialRampToValueAtTime(55, ctx.currentTime + 1.2);

    gain.gain.setValueAtTime(0.01, ctx.currentTime);
    gain.gain.linearRampToValueAtTime(0.35, ctx.currentTime + 0.1);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 1.4);

    osc.connect(filter);
    filter.connect(gain);
    gain.connect(ctx.destination);

    osc.start();
    osc.stop(ctx.currentTime + 1.4);
  } catch (e) {
    console.log("Audio not allowed or unsupported:", e);
  }
}

// DOM INITIALIZATION
document.addEventListener('DOMContentLoaded', () => {

  // ========================================================
  // 0. CINEMATIC 4-SHOT INTRO CONTROLLER (index.html)
  // ========================================================
  const introScreen = document.getElementById('intro-screen');
  if (introScreen) {
    const slides = document.querySelectorAll('.intro-slide');
    const timelineSteps = document.querySelectorAll('.timeline-step');
    const actTag = document.getElementById('intro-act-tag');
    const mainTitle = document.getElementById('intro-main-title');
    const soundToggleBtn = document.getElementById('intro-sound-toggle');
    const soundIcon = document.getElementById('sound-icon');
    const soundText = document.getElementById('sound-text');
    const skipBtn = document.getElementById('intro-skip-btn');
    const enterBtn = document.getElementById('intro-enter-btn');
    const replayBtn = document.getElementById('btn-replay-intro');

    let currentSlide = 0;
    let soundEnabled = true;
    let slideTimer = null;
    let progressTimer = null;
    const SLIDE_DURATION = 5000; // 5 seconds per shot

    // Play subtle cinematic atmospheric synthesizer sweep
    function playCinematicAtmosphere(index) {
      if (!soundEnabled) return;
      try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        const filter = ctx.createBiquadFilter();

        osc.type = index === 3 ? 'sawtooth' : 'sine';
        const freqs = [55, 65, 82, 110];
        osc.frequency.setValueAtTime(freqs[index] || 60, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime((freqs[index] || 60) * 1.5, ctx.currentTime + 0.8);

        filter.type = 'lowpass';
        filter.frequency.setValueAtTime(250, ctx.currentTime);

        gain.gain.setValueAtTime(0.001, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(0.12, ctx.currentTime + 0.3);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 2.2);

        osc.connect(filter);
        filter.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + 2.2);
      } catch (e) {}
    }

    // Go to specific shot index
    function goToSlide(index) {
      currentSlide = index;
      slides.forEach((s, i) => {
        s.classList.toggle('active', i === index);
      });

      timelineSteps.forEach((step, i) => {
        step.classList.toggle('active', i <= index);
        const fill = document.getElementById(`step-fill-${i}`);
        if (fill) {
          fill.style.width = i < index ? '100%' : (i === index ? '100%' : '0%');
        }
      });

      const activeSlide = slides[index];
      if (activeSlide && actTag && mainTitle) {
        actTag.textContent = activeSlide.getAttribute('data-caption');
        mainTitle.textContent = activeSlide.getAttribute('data-title');
      }

      playCinematicAtmosphere(index);
    }

    // Progress bar animation & auto advancement
    function startAutoSequence() {
      stopAutoSequence();
      let startTime = Date.now();

      function updateFill() {
        const elapsed = Date.now() - startTime;
        const percent = Math.min(100, (elapsed / SLIDE_DURATION) * 100);
        const activeFill = document.getElementById(`step-fill-${currentSlide}`);
        if (activeFill) {
          activeFill.style.width = percent + '%';
        }

        if (elapsed < SLIDE_DURATION) {
          progressTimer = requestAnimationFrame(updateFill);
        } else {
          // Advance to next slide
          if (currentSlide < slides.length - 1) {
            goToSlide(currentSlide + 1);
            startTime = Date.now();
            progressTimer = requestAnimationFrame(updateFill);
          } else {
            // Reached end of 4th shot: loop back smoothly to shot 0
            goToSlide(0);
            startTime = Date.now();
            progressTimer = requestAnimationFrame(updateFill);
          }
        }
      }

      progressTimer = requestAnimationFrame(updateFill);
    }

    function stopAutoSequence() {
      if (progressTimer) cancelAnimationFrame(progressTimer);
      if (slideTimer) clearTimeout(slideTimer);
    }

    // Exit Cinematic Screen with Supercar Rev Sound
    function exitIntro() {
      stopAutoSequence();
      if (soundEnabled) {
        playEngineRevAudio();
      }
      introScreen.classList.add('exiting');
      setTimeout(() => {
        introScreen.classList.add('hidden');
        introScreen.classList.remove('exiting');
      }, 750);
    }

    // Start intro sequence
    goToSlide(0);
    startAutoSequence();

    // Sound Toggle Button
    if (soundToggleBtn) {
      soundToggleBtn.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        if (soundEnabled) {
          soundIcon.className = 'fas fa-volume-high';
          soundText.textContent = 'Sound: ON';
          soundToggleBtn.classList.remove('muted');
          playCinematicAtmosphere(currentSlide);
        } else {
          soundIcon.className = 'fas fa-volume-xmark';
          soundText.textContent = 'Sound: OFF';
          soundToggleBtn.classList.add('muted');
        }
      });
    }

    // Step clicks on timeline
    timelineSteps.forEach(step => {
      step.addEventListener('click', () => {
        const targetStep = parseInt(step.getAttribute('data-step'), 10);
        goToSlide(targetStep);
        startAutoSequence();
      });
    });

    if (skipBtn) skipBtn.addEventListener('click', exitIntro);
    if (enterBtn) enterBtn.addEventListener('click', exitIntro);

    // Replay button in login footer
    if (replayBtn) {
      replayBtn.addEventListener('click', () => {
        introScreen.classList.remove('hidden');
        introScreen.classList.remove('exiting');
        goToSlide(0);
        startAutoSequence();
      });
    }
  }

  // ========================================================
  // A. AUTHENTICATION CONTROLLER (index.html)
  // ========================================================
  const loginView = document.getElementById('login-view');
  const signupView = document.getElementById('signup-view');
  const showSignupBtn = document.getElementById('show-signup');
  const showLoginBtn = document.getElementById('show-login');

  // Toggle between login and signup
  if (showSignupBtn && showLoginBtn && loginView && signupView) {
    showSignupBtn.addEventListener('click', () => {
      loginView.classList.add('hidden');
      signupView.classList.remove('hidden');
    });

    showLoginBtn.addEventListener('click', () => {
      signupView.classList.add('hidden');
      loginView.classList.remove('hidden');
    });
  }

  // Password Visibility Eye Toggle Buttons
  document.querySelectorAll('.pw-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      const icon = btn.querySelector('i');
      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          icon.className = 'far fa-eye-slash text-gold';
        } else {
          input.type = 'password';
          icon.className = 'far fa-eye';
        }
      }
    });
  });

  // Local Storage Database: usersDB = [{ email, username, password, walletBalance, garage }]
  let usersDB = JSON.parse(localStorage.getItem('usersDB')) || [];

  // Default to Sign Up view if no accounts exist yet
  if (usersDB.length === 0 && loginView && signupView) {
    loginView.classList.add('hidden');
    signupView.classList.remove('hidden');
  }

  // Handle Signup
  const signupForm = document.getElementById('signup-form');
  if (signupForm) {
    signupForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('signup-email').value.trim();
      const username = document.getElementById('signup-username').value.trim();
      const password = document.getElementById('signup-password').value;
      const confirmPassword = document.getElementById('signup-confirm-password').value;

      if (password !== confirmPassword) {
        showToast("Passwords do not match!", "error");
        return;
      }

      if (password.length < 4) {
        showToast("Password must be at least 4 characters.", "error");
        return;
      }

      const existingUser = usersDB.find(u =>
        u.username.toLowerCase() === username.toLowerCase() ||
        u.email.toLowerCase() === email.toLowerCase()
      );

      if (existingUser) {
        showToast("A collector with this email or username already exists!", "error");
        return;
      }

      // Create new user with full $999T simulated pocket money & empty garage
      const newUser = {
        email,
        username,
        password,
        walletBalance: DEFAULT_WALLET_AMOUNT,
        garage: []
      };

      usersDB.push(newUser);
      localStorage.setItem('usersDB', JSON.stringify(usersDB));
      localStorage.setItem('currentUser', username);

      showToast(`Welcome ${username}! VIP account created with $999T Pocket Money.`, "success");

      setTimeout(() => {
        window.location.href = 'home.html';
      }, 900);
    });
  }

  // Handle Login
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const usernameInput = document.getElementById('login-username').value.trim();
      const passwordInput = document.getElementById('login-password').value;

      const user = usersDB.find(u =>
        u.username.toLowerCase() === usernameInput.toLowerCase() &&
        u.password === passwordInput
      );

      if (user) {
        localStorage.setItem('currentUser', user.username);
        showToast(`Access granted. Welcome back, Collector ${user.username}!`, "success");

        const submitBtn = loginForm.querySelector('.btn-primary');
        if (submitBtn) {
          submitBtn.innerHTML = '<span>Entering Atelier...</span> <i class="fas fa-spinner fa-spin ms-2"></i>';
        }

        setTimeout(() => {
          window.location.href = 'home.html';
        }, 700);
      } else {
        showToast("Invalid Collector credentials. Please verify your details.", "error");
      }
    });
  }

  // ========================================================
  // B. VIP DASHBOARD & SHOWROOM CONTROLLER (home.html)
  // ========================================================
  const userDisplay = document.getElementById('user-display');
  if (userDisplay) {
    const currentUsername = localStorage.getItem('currentUser');
    if (!currentUsername) {
      window.location.href = 'index.html';
      return;
    }

    // Load or initialize active user record
    let currentUserObj = usersDB.find(u => u.username.toLowerCase() === currentUsername.toLowerCase());
    if (!currentUserObj) {
      currentUserObj = {
        username: currentUsername,
        email: `${currentUsername}@apex.club`,
        password: 'password',
        walletBalance: DEFAULT_WALLET_AMOUNT,
        garage: []
      };
      usersDB.push(currentUserObj);
      localStorage.setItem('usersDB', JSON.stringify(usersDB));
    }

    // Ensure wallet and garage structures exist
    if (currentUserObj.walletBalance === undefined || isNaN(currentUserObj.walletBalance)) {
      currentUserObj.walletBalance = DEFAULT_WALLET_AMOUNT;
    }
    if (!Array.isArray(currentUserObj.garage)) {
      currentUserObj.garage = [];
    }

    // Helper to persist user changes
    function saveCurrentUser() {
      const idx = usersDB.findIndex(u => u.username.toLowerCase() === currentUserObj.username.toLowerCase());
      if (idx !== -1) {
        usersDB[idx] = currentUserObj;
      }
      localStorage.setItem('usersDB', JSON.stringify(usersDB));
    }

    // Populate user profile info
    userDisplay.textContent = currentUserObj.username;
    const welcomeHeader = document.getElementById('welcome-username');
    if (welcomeHeader) welcomeHeader.textContent = currentUserObj.username;

    const avatarInitial = document.getElementById('user-avatar-initial');
    if (avatarInitial) avatarInitial.textContent = currentUserObj.username.charAt(0).toUpperCase();

    // Update Dashboard Metrics & Navbar
    function updateMetricsUI() {
      const navWallet = document.getElementById('navbar-wallet-amount');
      const statWallet = document.getElementById('stat-wallet-balance');
      const statGarage = document.getElementById('stat-garage-count');
      const navGarage = document.getElementById('nav-garage-count');
      const statFleet = document.getElementById('stat-fleet-value');

      const formattedBal = formatCurrency(currentUserObj.walletBalance);
      if (navWallet) navWallet.textContent = formattedBal;
      if (statWallet) statWallet.textContent = formattedBal;

      const garageCount = currentUserObj.garage.length;
      if (statGarage) statGarage.textContent = garageCount;
      if (navGarage) navGarage.textContent = garageCount;

      const fleetTotal = currentUserObj.garage.reduce((acc, car) => acc + (car.price || 0), 0);
      if (statFleet) statFleet.textContent = formatCurrency(fleetTotal);
    }

    updateMetricsUI();

    // Quick Deposit $100M
    const quickDepositBtn = document.getElementById('quick-deposit-btn');
    if (quickDepositBtn) {
      quickDepositBtn.addEventListener('click', () => {
        currentUserObj.walletBalance += 100000000;
        saveCurrentUser();
        updateMetricsUI();
        showToast("Added +$100,000,000 App Pocket Money!", "success");
      });
    }

    // Wallet pill click: options to reset or add
    const walletPillBtn = document.getElementById('wallet-pill-btn');
    if (walletPillBtn) {
      walletPillBtn.addEventListener('click', () => {
        currentUserObj.walletBalance = DEFAULT_WALLET_AMOUNT;
        saveCurrentUser();
        updateMetricsUI();
        showToast("App Pocket Money refreshed to $999,999,999,999,999!", "success");
      });
    }

    // Navigation Tabs (Showroom vs Garage)
    const tabShowroomBtn = document.getElementById('btn-tab-showroom');
    const tabGarageBtn = document.getElementById('btn-tab-garage');
    const viewShowroom = document.getElementById('view-showroom');
    const viewGarage = document.getElementById('view-garage');

    function switchTab(target) {
      if (target === 'showroom') {
        tabShowroomBtn.classList.add('active');
        tabGarageBtn.classList.remove('active');
        viewShowroom.classList.remove('hidden');
        viewGarage.classList.add('hidden');
      } else {
        tabGarageBtn.classList.add('active');
        tabShowroomBtn.classList.remove('active');
        viewGarage.classList.remove('hidden');
        viewShowroom.classList.add('hidden');
        renderGarage();
      }
    }

    if (tabShowroomBtn && tabGarageBtn) {
      tabShowroomBtn.addEventListener('click', () => switchTab('showroom'));
      tabGarageBtn.addEventListener('click', () => switchTab('garage'));
    }

    const viewGarageStatBtn = document.getElementById('view-garage-stat-btn');
    if (viewGarageStatBtn) {
      viewGarageStatBtn.addEventListener('click', () => switchTab('garage'));
    }

    const garageGoShowroomBtn = document.getElementById('garage-go-showroom-btn');
    if (garageGoShowroomBtn) {
      garageGoShowroomBtn.addEventListener('click', () => switchTab('showroom'));
    }

    const emptyBrowseBtn = document.getElementById('btn-empty-browse-showroom');
    if (emptyBrowseBtn) {
      emptyBrowseBtn.addEventListener('click', () => switchTab('showroom'));
    }

    // Logout Button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => {
        localStorage.removeItem('currentUser');
        showToast("Signing out of VIP Atelier...", "info");
        setTimeout(() => {
          window.location.href = 'index.html';
        }, 500);
      });
    }

    // ========================================================
    // C. SHOWROOM CATALOG FILTERING & RENDERING
    // ========================================================
    let currentBrand = 'All';
    let currentSort = 'price-desc'; // Default: Highest to Lowest as required!
    let maxPrice = 5000000;
    let searchQuery = '';

    const brandPillsContainer = document.getElementById('brand-pills-container');
    const supercarsGrid = document.getElementById('supercars-grid');
    const showingCount = document.getElementById('showing-count');
    const searchInput = document.getElementById('car-search-input');
    const sortSelect = document.getElementById('car-sort-select');
    const priceSlider = document.getElementById('price-range-slider');
    const priceSliderLabel = document.getElementById('price-slider-label');

    // Build Brand Filter Pills
    const allBrands = ['All', ...new Set(CARS_DATABASE.map(c => c.brand))].sort();
    if (brandPillsContainer) {
      brandPillsContainer.innerHTML = '';
      allBrands.forEach(brand => {
        const pill = document.createElement('button');
        pill.className = `brand-pill ${brand === currentBrand ? 'active' : ''}`;
        pill.textContent = brand;
        pill.addEventListener('click', () => {
          currentBrand = brand;
          document.querySelectorAll('.brand-pill').forEach(p => p.classList.remove('active'));
          pill.classList.add('active');
          renderShowroom();
        });
        brandPillsContainer.appendChild(pill);
      });
    }

    // Setup Filter Controls
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        searchQuery = e.target.value.toLowerCase().trim();
        renderShowroom();
      });
    }

    if (sortSelect) {
      sortSelect.addEventListener('change', (e) => {
        currentSort = e.target.value;
        renderShowroom();
      });
    }

    if (priceSlider && priceSliderLabel) {
      priceSlider.addEventListener('input', (e) => {
        maxPrice = Number(e.target.value);
        priceSliderLabel.textContent = formatCurrency(maxPrice);
        renderShowroom();
      });
    }

    // Render Showroom Grid
    function renderShowroom() {
      if (!supercarsGrid) return;

      let filtered = CARS_DATABASE.filter(car => {
        const matchesBrand = (currentBrand === 'All' || car.brand.toLowerCase() === currentBrand.toLowerCase());
        const matchesPrice = car.price <= maxPrice;
        const matchesSearch = !searchQuery ||
          car.name.toLowerCase().includes(searchQuery) ||
          car.brand.toLowerCase().includes(searchQuery) ||
          car.engine.toLowerCase().includes(searchQuery);
        return matchesBrand && matchesPrice && matchesSearch;
      });

      // Sort
      if (currentSort === 'price-desc') {
        filtered.sort((a, b) => b.price - a.price);
      } else if (currentSort === 'price-asc') {
        filtered.sort((a, b) => a.price - b.price);
      } else if (currentSort === 'hp-desc') {
        filtered.sort((a, b) => (b.hpNum || 0) - (a.hpNum || 0));
      } else if (currentSort === 'speed-desc') {
        filtered.sort((a, b) => (b.topSpeedNum || 0) - (a.topSpeedNum || 0));
      }

      if (showingCount) showingCount.textContent = filtered.length;

      if (filtered.length === 0) {
        supercarsGrid.innerHTML = `
          <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;" class="glass-panel">
            <i class="fas fa-car-rear fa-3x text-gold mb-3"></i>
            <h3 style="margin-top:12px;">No Supercars Found</h3>
            <p style="color:var(--text-secondary);">Try adjusting your search criteria or price slider.</p>
          </div>
        `;
        return;
      }

      supercarsGrid.innerHTML = filtered.map(car => `
        <div class="car-card">
          <div class="card-media">
            <img src="${car.image}" alt="${car.name}" loading="lazy">
            <span class="card-badge">${car.badge}</span>
            <span class="card-marque-pill">${car.brand}</span>
          </div>
          <div class="card-body">
            <div class="card-header-row">
              <span class="card-marque">${car.brand}</span>
              <h3 class="card-title">${car.name}</h3>
            </div>
            <p class="card-desc">${car.description}</p>
            <div class="card-specs-bar">
              <div class="spec-item">
                <span class="spec-key">0-100 km/h</span>
                <span class="spec-val">${car.acceleration}</span>
              </div>
              <div class="spec-item">
                <span class="spec-key">Top Speed</span>
                <span class="spec-val">${car.topSpeed}</span>
              </div>
              <div class="spec-item">
                <span class="spec-key">Power</span>
                <span class="spec-val">${car.horsepower}</span>
              </div>
            </div>
            <div class="card-footer">
              <div class="card-price-group">
                <span class="price-sub">Starting MSRP</span>
                <span class="price-main">${formatCurrency(car.price)}</span>
              </div>
              <button class="btn-order-atelier" onclick="openAtelierModal('${car.id}')">
                <i class="fas fa-sliders"></i>
                <span>Configure & Buy</span>
              </button>
            </div>
          </div>
        </div>
      `).join('');
    }

    renderShowroom();

    // ========================================================
    // D. ATELIER ORDER & COLOR CUSTOMIZER MODAL
    // ========================================================
    let selectedCar = null;
    let selectedColor = null;

    const atelierModal = document.getElementById('atelier-modal');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const btnCancelModal = document.getElementById('btn-cancel-modal');
    const btnConfirmPurchase = document.getElementById('btn-confirm-purchase');

    window.openAtelierModal = function(carId) {
      selectedCar = CARS_DATABASE.find(c => c.id === carId);
      if (!selectedCar || !atelierModal) return;

      selectedColor = selectedCar.colors[0];

      // Populate Modal Fields
      document.getElementById('modal-car-img').src = selectedCar.image;
      document.getElementById('modal-car-badge').textContent = selectedCar.badge;
      document.getElementById('modal-spec-speed').textContent = selectedCar.topSpeed;
      document.getElementById('modal-spec-acc').textContent = selectedCar.acceleration;
      document.getElementById('modal-spec-hp').textContent = selectedCar.horsepower;
      document.getElementById('modal-car-brand').textContent = selectedCar.brand.toUpperCase();
      document.getElementById('modal-car-name').textContent = selectedCar.name;
      document.getElementById('modal-car-price').textContent = formatCurrency(selectedCar.price);
      document.getElementById('summary-msrp').textContent = formatCurrency(selectedCar.price);
      document.getElementById('summary-total').textContent = formatCurrency(selectedCar.price);
      document.getElementById('modal-user-wallet').textContent = formatCurrency(currentUserObj.walletBalance);

      // Render Swatches
      const swatchesContainer = document.getElementById('modal-swatches');
      const activeColorLabel = document.getElementById('modal-selected-color-name');
      if (activeColorLabel) activeColorLabel.textContent = selectedColor.name;

      if (swatchesContainer) {
        swatchesContainer.innerHTML = selectedCar.colors.map((c, i) => `
          <button type="button" class="swatch-btn ${i === 0 ? 'active' : ''}" 
                  style="background-color: ${c.hex};" 
                  title="${c.name}" 
                  onclick="selectAtelierColor(${i})"></button>
        `).join('');
      }

      atelierModal.classList.remove('hidden');
    };

    window.selectAtelierColor = function(colorIndex) {
      if (!selectedCar || !selectedCar.colors[colorIndex]) return;
      selectedColor = selectedCar.colors[colorIndex];

      const activeColorLabel = document.getElementById('modal-selected-color-name');
      if (activeColorLabel) activeColorLabel.textContent = selectedColor.name;

      // Update active swatch button
      const buttons = document.querySelectorAll('.swatch-btn');
      buttons.forEach((b, i) => b.classList.toggle('active', i === colorIndex));

      // Border glow on car image matching chosen color
      const imgContainer = document.querySelector('.modal-img-container');
      if (imgContainer) {
        imgContainer.style.borderColor = selectedColor.hex;
      }
    };

    function closeAtelierModal() {
      if (atelierModal) atelierModal.classList.add('hidden');
      selectedCar = null;
      selectedColor = null;
    }

    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeAtelierModal);
    if (btnCancelModal) btnCancelModal.addEventListener('click', closeAtelierModal);

    // Confirm Purchase
    if (btnConfirmPurchase) {
      btnConfirmPurchase.addEventListener('click', () => {
        if (!selectedCar || !selectedColor) return;

        if (currentUserObj.walletBalance < selectedCar.price) {
          showToast("Insufficient App Pocket Money! Click the '+ $100M' button to deposit more funds.", "error");
          return;
        }

        // Deduct balance
        currentUserObj.walletBalance -= selectedCar.price;

        // Add to garage
        const newAcquisition = {
          orderId: 'APX-' + Math.floor(100000 + Math.random() * 900000),
          carId: selectedCar.id,
          name: selectedCar.name,
          brand: selectedCar.brand,
          price: selectedCar.price,
          image: selectedCar.image,
          color: selectedColor,
          specs: {
            topSpeed: selectedCar.topSpeed,
            acceleration: selectedCar.acceleration,
            horsepower: selectedCar.horsepower,
            engine: selectedCar.engine,
            transmission: selectedCar.transmission
          },
          purchaseDate: new Date().toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
          })
        };

        currentUserObj.garage.unshift(newAcquisition);
        saveCurrentUser();
        updateMetricsUI();
        closeAtelierModal();

        showToast(`Acquisition confirmed! ${selectedCar.name} delivered to your Private Garage!`, "success");
        playEngineRevAudio();

        // Switch to garage view after short delay to admire vehicle
        setTimeout(() => {
          switchTab('garage');
        }, 800);
      });
    }

    // ========================================================
    // E. MY PRIVATE GARAGE CONTROLLER
    // ========================================================
    const garageGrid = document.getElementById('garage-grid');
    const emptyGarageState = document.getElementById('empty-garage-state');

    function renderGarage() {
      if (!garageGrid || !emptyGarageState) return;

      const garage = currentUserObj.garage;

      if (garage.length === 0) {
        garageGrid.innerHTML = '';
        emptyGarageState.classList.remove('hidden');
        return;
      }

      emptyGarageState.classList.add('hidden');
      garageGrid.innerHTML = garage.map((item, index) => `
        <div class="garage-card">
          <div class="garage-media">
            <img src="${item.image}" alt="${item.name}">
            <span class="garage-order-badge"><i class="fas fa-certificate text-gold me-1"></i> ${item.orderId}</span>
          </div>
          <div class="garage-body">
            <span class="card-marque">${item.brand}</span>
            <h3 class="card-title" style="font-size:18px; margin-bottom: 10px;">${item.name}</h3>

            <div class="color-badge-pill">
              <span class="color-dot" style="background-color: ${item.color.hex};"></span>
              <span>${item.color.name}</span>
            </div>

            <div style="font-size:12px; color:var(--text-muted); margin-bottom: 12px;">
              <i class="far fa-clock me-1"></i> Acquired: ${item.purchaseDate}
            </div>

            <div class="garage-actions-row">
              <button class="btn-rev-engine" onclick="revEngineSound('${item.name}')">
                <i class="fas fa-volume-high me-1"></i> Rev Engine
              </button>
              <button class="btn-secondary" onclick="openSpecsModal(${index})">
                <i class="fas fa-circle-info me-1"></i> Specs
              </button>
            </div>
          </div>
        </div>
      `).join('');
    }

    window.revEngineSound = function(carName) {
      playEngineRevAudio();
      showToast(`Revving ${carName} exhaust note! 🏎️💨`, "info");
    };

    // Specs Modal
    const specsModal = document.getElementById('specs-modal');
    const specsModalClose = document.getElementById('specs-modal-close');
    const specsModalRevBtn = document.getElementById('specs-modal-rev-btn');

    window.openSpecsModal = function(garageIndex) {
      const car = currentUserObj.garage[garageIndex];
      if (!car || !specsModal) return;

      document.getElementById('specs-modal-img').src = car.image;
      document.getElementById('specs-modal-name').textContent = car.name;
      document.getElementById('specs-modal-brand').textContent = `${car.brand.toUpperCase()} • ${car.color.name}`;

      const list = document.getElementById('specs-details-list');
      list.innerHTML = `
        <div class="specs-row"><span>Engine:</span><strong class="text-gold">${car.specs.engine}</strong></div>
        <div class="specs-row"><span>Power Output:</span><strong>${car.specs.horsepower}</strong></div>
        <div class="specs-row"><span>Acceleration:</span><strong>${car.specs.acceleration}</strong></div>
        <div class="specs-row"><span>Top Speed:</span><strong>${car.specs.topSpeed}</strong></div>
        <div class="specs-row"><span>Transmission:</span><strong>${car.specs.transmission}</strong></div>
        <div class="specs-row"><span>Certificate ID:</span><span class="text-cyan">${car.orderId}</span></div>
      `;

      specsModal.classList.remove('hidden');
    };

    if (specsModalClose) {
      specsModalClose.addEventListener('click', () => {
        specsModal.classList.add('hidden');
      });
    }

    if (specsModalRevBtn) {
      specsModalRevBtn.addEventListener('click', () => {
        playEngineRevAudio();
        showToast("Exhaust system unleashed! 🔊", "info");
      });
    }
  }
});

