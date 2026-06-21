/**
 * FiscalAmb Pro — Image Gallery System
 * Gerenciamento dinâmico de imagens, galeria com filtros e lightbox.
 *
 * Para adicionar novas imagens: inclua arquivos na pasta images/galeria/
 * e adicione uma entrada no array GALLERY_IMAGES abaixo.
 */

/* ── Configuração das Imagens ─────────────────────────────── */
const HERO_BG   = 'images/hero-bg.svg';

/*
 * FOTOS REAIS — salve os arquivos em images/ com estes nomes exatos:
 *   fiscal-obras.jpg         → Fiscal feminina com tablet em obra
 *   fiscal-ambiental.jpg     → Fiscal masculino com tablet em parque
 *   assinatura-digital.jpg   → Dois fiscais + cidadão assinando tablet
 *   dashboard-gestao.jpg     → Gestora no painel de indicadores
 */
const REAL_PHOTOS = {
  fiscalObras:       'images/galeria/fiscal-obras-sp.jpg',
  fiscalAmbiental:   'images/galeria/fiscalizacao-floresta.jpg',
  assinaturaDigital: 'images/galeria/assinatura-rural.jpg',
  dashboardGestao:   'images/galeria/dashboard-sisfim.jpg',
  mapaCoi:           'images/galeria/mapa-coi.jpg',
};

const GALLERY_IMAGES = [
  /* ── Fotos campo / obras ── */
  {
    src:   'images/galeria/fiscal-obras-sp.jpg',
    title: 'Fiscal de Obras — Prefeitura de São Paulo',
    desc:  'Fiscal municipal da Prefeitura de São Paulo registrando vistoria com tablet robusto em obra vertical.',
    cat:   'campo',
    tag:   'Obras',
  },
  {
    src:   'images/galeria/fiscal-curitiba.jpg',
    title: 'Fiscalização Municipal — Curitiba',
    desc:  'Fiscal ambiental da Prefeitura de Curitiba vistoriando obra com tablet e sistema integrado.',
    cat:   'campo',
    tag:   'Obras',
  },
  {
    src:   'images/galeria/fiscal-obras.jpg',
    title: 'Vistoria de Obra em Campo',
    desc:  'Fiscal municipal registrando vistoria de obra em campo com tablet robusto e sistema integrado.',
    cat:   'campo',
    tag:   'Obras',
  },
  /* ── Fotos ambiental ── */
  {
    src:   'images/galeria/fiscalizacao-floresta.jpg',
    title: 'Fiscalização em Área de Preservação',
    desc:  'Equipe de fiscalização ambiental monitorando área de preservação com tablet, GPS e câmera profissional.',
    cat:   'ambiental',
    tag:   'Ambiental',
  },
  {
    src:   'images/galeria/fiscal-ambiental.jpg',
    title: 'Fiscal Ambiental em Área Verde',
    desc:  'Fiscal ambiental monitorando área de preservação com app mobile, câmera e GPS integrados.',
    cat:   'ambiental',
    tag:   'Ambiental',
  },
  /* ── Assinatura digital ── */
  {
    src:   'images/galeria/assinatura-rural.jpg',
    title: 'Assinatura Digital — Fiscalização Rural',
    desc:  'Agente ambiental coletando assinatura digital do proprietário rural direto no tablet, com validade jurídica.',
    cat:   'campo',
    tag:   'Campo',
  },
  {
    src:   'images/galeria/assinatura-digital.jpg',
    title: 'Assinatura Digital em Campo',
    desc:  'Emissão de notificação e coleta de assinatura digital diretamente no tablet, em campo.',
    cat:   'campo',
    tag:   'Campo',
  },
  /* ── Gestão / dashboard ── */
  {
    src:   'images/galeria/dashboard-sisfim.jpg',
    title: 'SISFIM — Painel de Gestão Municipal',
    desc:  'Sistema de Fiscalização Municipal de Curitiba: KPIs em tempo real, mapa de ações e relatórios gerenciais.',
    cat:   'gestao',
    tag:   'Gestão',
  },
  {
    src:   'images/galeria/dashboard-gestao.jpg',
    title: 'Painel de Indicadores em Tela Grande',
    desc:  'Gestora visualizando painel de indicadores e relatórios gerenciais do Sistema de Fiscalização.',
    cat:   'gestao',
    tag:   'Gestão',
  },
  /* ── Georreferenciamento ── */
  {
    src:   'images/galeria/mapa-coi.jpg',
    title: 'Centro de Operações Integradas — COI',
    desc:  'Mapa de ocorrências em tempo real no Centro de Operações com 7.845 pontos ativos e 1.320 fiscalizações em andamento.',
    cat:   'geo',
    tag:   'Georreferenciamento',
  },
  {
    src:   'images/galeria/geo-01.svg',
    title: 'Dashboard Cartográfico de Fiscalização',
    desc:  'Mapa interativo com todos os pontos de fiscalização georreferenciados: infrações, preservação, denúncias e embargos.',
    cat:   'geo',
    tag:   'Georreferenciamento',
  },
];

/* hero-bg.svg é aplicado via CSS — sem JS para compatibilidade com file:// */

/* ── Gallery Builder ──────────────────────────────────────── */
const FILTERS = [
  { key: 'todos',      label: 'Todos'               },
  { key: 'campo',      label: 'Campo & Obras'        },
  { key: 'ambiental',  label: 'Ambiental'            },
  { key: 'gestao',     label: 'Gestão'               },
  { key: 'geo',        label: 'Georreferenciamento'  },
];

function buildFilters(container) {
  const wrap = document.createElement('div');
  wrap.className = 'gallery-filters';

  FILTERS.forEach((f, i) => {
    const btn = document.createElement('button');
    btn.className = 'gf-btn' + (i === 0 ? ' active' : '');
    btn.dataset.filter = f.key;
    btn.textContent = f.label;
    btn.addEventListener('click', () => {
      container.querySelectorAll('.gf-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filterGallery(f.key);
    });
    wrap.appendChild(btn);
  });
  container.insertAdjacentElement('beforebegin', wrap);
}

function buildCards(grid) {
  GALLERY_IMAGES.forEach((img, i) => {
    const card = document.createElement('div');
    card.className = 'g-card reveal';
    card.dataset.cat = img.cat;
    card.style.transitionDelay = `${(i % 3) * 0.1}s`;

    card.innerHTML = `
      <div class="g-img-wrap">
        <img src="${img.src}" alt="${img.title}" class="g-img"/>
        <div class="g-overlay">
          <span class="g-tag">${img.tag}</span>
          <button class="g-zoom" aria-label="Ampliar imagem">⤢</button>
        </div>
      </div>
      <div class="g-info">
        <h4 class="g-title">${img.title}</h4>
        <p class="g-desc">${img.desc}</p>
      </div>`;

    card.querySelector('.g-zoom').addEventListener('click', (e) => {
      e.stopPropagation();
      openLightbox(i);
    });
    card.addEventListener('click', () => openLightbox(i));
    grid.appendChild(card);
  });

  // Trigger reveal observer on new cards
  document.querySelectorAll('.g-card.reveal').forEach(el => revealObserver.observe(el));
}

function filterGallery(cat) {
  document.querySelectorAll('.g-card').forEach(card => {
    const match = cat === 'todos' || card.dataset.cat === cat;
    card.style.transition = 'opacity .35s, transform .35s';
    if (match) {
      card.style.opacity = '1';
      card.style.transform = 'scale(1)';
      card.style.display = '';
    } else {
      card.style.opacity = '0';
      card.style.transform = 'scale(.94)';
      setTimeout(() => { if (card.dataset.cat !== cat && cat !== 'todos') card.style.display = 'none'; }, 350);
    }
  });
}

/* ── Lightbox ─────────────────────────────────────────────── */
let currentIdx = 0;

function createLightbox() {
  const lb = document.createElement('div');
  lb.id = 'lightbox';
  lb.innerHTML = `
    <div class="lb-backdrop"></div>
    <div class="lb-panel">
      <button class="lb-close" aria-label="Fechar">✕</button>
      <button class="lb-prev"  aria-label="Anterior">‹</button>
      <button class="lb-next"  aria-label="Próximo">›</button>
      <div class="lb-img-wrap">
        <img class="lb-img" src="" alt=""/>
      </div>
      <div class="lb-caption">
        <span class="lb-tag"></span>
        <strong class="lb-title"></strong>
        <p class="lb-desc"></p>
      </div>
      <div class="lb-counter"></div>
    </div>`;

  document.body.appendChild(lb);

  lb.querySelector('.lb-backdrop').addEventListener('click', closeLightbox);
  lb.querySelector('.lb-close').addEventListener('click', closeLightbox);
  lb.querySelector('.lb-prev').addEventListener('click', () => stepLightbox(-1));
  lb.querySelector('.lb-next').addEventListener('click', () => stepLightbox(+1));

  document.addEventListener('keydown', (e) => {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  stepLightbox(-1);
    if (e.key === 'ArrowRight') stepLightbox(+1);
  });
}

function openLightbox(idx) {
  currentIdx = idx;
  const lb = document.getElementById('lightbox');
  updateLightbox();
  lb.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.body.style.overflow = '';
}

function stepLightbox(dir) {
  currentIdx = (currentIdx + dir + GALLERY_IMAGES.length) % GALLERY_IMAGES.length;
  updateLightbox();
}

function updateLightbox() {
  const img  = GALLERY_IMAGES[currentIdx];
  const lb   = document.getElementById('lightbox');
  const el   = lb.querySelector('.lb-img');

  el.style.opacity = '0';
  el.src = img.src;
  el.alt = img.title;
  el.onload = () => { el.style.opacity = '1'; };

  lb.querySelector('.lb-tag').textContent    = img.tag;
  lb.querySelector('.lb-title').textContent  = img.title;
  lb.querySelector('.lb-desc').textContent   = img.desc;
  lb.querySelector('.lb-counter').textContent =
    `${currentIdx + 1} / ${GALLERY_IMAGES.length}`;
}

/* ── Shared Reveal Observer (exported for use in index.html) ─ */
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('in'); });
}, { threshold: 0.08 });

/* ── Inject real photos into site sections ────────────────── */
function injectSectionPhotos() {
  // 1. App Mobile section — replace CSS mockup with real inspector photo
  const tabScene = document.querySelector('.tab-scene');
  if (tabScene) {
    tabScene.innerHTML = `
      <div class="rp-frame">
        <img src="${REAL_PHOTOS.fiscalObras}"
             alt="Fiscal municipal com tablet em campo"
             class="rp-img" loading="lazy"
             onerror="this.closest('.rp-frame').classList.add('rp-fallback')"/>
        <div class="rp-badge rp-badge-top">
          <span class="rp-dot"></span>Sistema Ativo
        </div>
        <div class="rp-badge rp-badge-bot">
          <div class="rp-badge-num">GPS</div>
          <div class="rp-badge-sub">Localização capturada</div>
        </div>
      </div>`;
  }

  // 2. Geo section — add real COI map photo + forest inspector photo beside map
  const geoMap = document.querySelector('.geo-map-box');
  if (geoMap) {
    const coiEl = document.createElement('div');
    coiEl.className = 'geo-real-photo';
    coiEl.innerHTML = `
      <img src="${REAL_PHOTOS.mapaCoi}"
           alt="Centro de Operações Integradas — Mapa de Ocorrências em Tempo Real"
           class="rp-img geo-rp-img" loading="lazy"
           onerror="this.closest('.geo-real-photo').style.display='none'"/>
      <div class="geo-rp-badge">Centro de Operações — COI</div>`;
    geoMap.insertAdjacentElement('afterend', coiEl);

    const forestEl = document.createElement('div');
    forestEl.className = 'geo-real-photo reveal';
    forestEl.innerHTML = `
      <img src="${REAL_PHOTOS.fiscalAmbiental}"
           alt="Equipe de fiscalização ambiental em área de preservação"
           class="rp-img geo-rp-img" loading="lazy"
           onerror="this.closest('.geo-real-photo').style.display='none'"/>`;
    coiEl.insertAdjacentElement('afterend', forestEl);
  }

  // 3. Dashboard section — replace fake CSS chart with real photo
  const dbFrame = document.querySelector('.db-frame');
  if (dbFrame) {
    const photoPanel = document.createElement('div');
    photoPanel.className = 'db-real-photo reveal';
    photoPanel.innerHTML = `
      <img src="${REAL_PHOTOS.dashboardGestao}"
           alt="Painel de gestão do sistema de fiscalização"
           class="rp-img" loading="lazy"
           onerror="this.closest('.db-real-photo').style.display='none'"/>
      <div class="db-photo-badge">
        <span>📊</span> Sistema de Fiscalização Municipal
      </div>`;
    dbFrame.insertAdjacentElement('afterend', photoPanel);
  }

  // 4. "Como Funciona" section — add signing photo as a visual accent
  const cfWrap = document.querySelector('.cf-wrap');
  if (cfWrap) {
    const signingEl = document.createElement('div');
    signingEl.className = 'cf-real-photo reveal';
    signingEl.innerHTML = `
      <img src="${REAL_PHOTOS.assinaturaDigital}"
           alt="Fiscais emitindo notificação com assinatura digital"
           class="rp-img" loading="lazy"
           onerror="this.closest('.cf-real-photo').style.display='none'"/>
      <div class="cf-photo-caption">
        <strong>Emissão Digital em Campo</strong>
        Notificação assinada digitalmente, sem papel, com validade jurídica imediata.
      </div>`;
    cfWrap.appendChild(signingEl);
  }

  // Attach revealObserver to newly added elements
  document.querySelectorAll('.db-real-photo.reveal, .cf-real-photo.reveal, .geo-real-photo.reveal').forEach(el => {
    revealObserver.observe(el);
  });
}

/* ── Init ─────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Inject real photos into sections
  injectSectionPhotos();

  // Gallery section
  const section = document.getElementById('galeria');
  if (!section) return;

  const grid = section.querySelector('#gallery-grid');
  if (!grid) return;

  buildFilters(grid);
  buildCards(grid);
  createLightbox();
});
