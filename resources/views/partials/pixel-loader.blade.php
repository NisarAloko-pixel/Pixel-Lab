<script>
  window.PL_LOADER_MIN = {{ session('just_logged_in') ? 5000 : 1200 }};
</script>

@verbatim
<!--
  PIXEL LAB · 3D PRINTING MANAGEMENT SYSTEM · Loading overlay
  ------------------------------------------------------------
  Zero dependencies. One 4-second seamless loop. Light theme.

  Laravel usage
    1. Copy the <style>, the #pl-loader markup and the <script> into
       resources/views/partials/pixel-loader.blade.php
    2. @include('partials.pixel-loader') just before </body> in your layout.
    3. Add  data-autohide  to #pl-loader to fade it out on window load,
       or control it manually:
         PixelLabLoader.show();   // e.g. before an AJAX call / navigation
         PixelLabLoader.hide();

  Swap in the real logo
    The real Pixel Lab logo is embedded twice as base64 WebP:
      #plLogoColor  full-colour mark (what gets printed)
      #plLogoSil    white silhouette (masks the layer lines / hot bead / sheen)
    To change the logo, replace both images (same size, same aspect ratio).
-->

<style>, the #pl-loader markup and the <script> into
       resources/views/partials/pixel-loader.blade.php
    2. @include('partials.pixel-loader') just before </body> in your layout.
    3. Add  data-autohide  to #pl-loader to fade it out on window load,
       or control it manually:
         PixelLabLoader.show();   // e.g. before an AJAX call / navigation
         PixelLabLoader.hide();

  Swap in the real logo
    The real Pixel Lab logo is embedded twice as base64 WebP:
      #plLogoColor  full-colour mark (what gets printed)
      #plLogoSil    white silhouette (masks the layer lines / hot bead / sheen)
    To change the logo, replace both images (same size, same aspect ratio).
-->

<style>
  html, body { margin: 0; height: 100%; background: #f4f7fb; }

  .pl-loader {
    --pl-blue: #1470e8;
    --pl-blue-soft: #2f9bff;
    --pl-ink: #0b1f4a;
    position: fixed; inset: 0; z-index: 9999;
    display: grid; place-items: center; overflow: hidden;
    font-family: "Inter", "SF Pro Display", "Segoe UI", system-ui, -apple-system, Roboto, sans-serif;
    color: #10203c;
    background:
      radial-gradient(52% 46% at 50% 40%, rgba(47, 155, 255, .14), transparent 72%),
      radial-gradient(120% 90% at 50% 0%, #ffffff 0%, #f2f6fb 55%, #e3e9f2 100%);
    transition: opacity .55s ease, visibility .55s ease;
  }
  .pl-loader::before {            /* faint engineering dot-grid, fades toward edges */
    content: ""; position: absolute; inset: 0; pointer-events: none;
    background-image: radial-gradient(rgba(20, 45, 90, .12) 1px, transparent 1.2px);
    background-size: 26px 26px;
    -webkit-mask-image: radial-gradient(60% 55% at 50% 45%, #000 0%, transparent 100%);
            mask-image: radial-gradient(60% 55% at 50% 45%, #000 0%, transparent 100%);
  }
  .pl-loader.is-hidden { opacity: 0; visibility: hidden; pointer-events: none; }

  .pl-stack { position: relative; width: min(460px, 90vw); display: flex; flex-direction: column; align-items: center; }
  .pl-stage { position: relative; width: 100%; aspect-ratio: 400 / 320; }
  .pl-stage svg { position: absolute; inset: 0; width: 100%; height: 100%; overflow: visible; }

  /* floating "data" particles */
  .pl-particles { position: absolute; inset: 0; pointer-events: none; }
  .pl-particles i {
    position: absolute; left: var(--x); top: var(--y);
    width: var(--s); height: var(--s); border-radius: 1px;
    background: #2f9bff; opacity: 0;
    box-shadow: 0 0 6px 1px rgba(47, 155, 255, .35);
    animation: pl-float var(--d) linear var(--dl) infinite;
  }
  @keyframes pl-float {
    0%   { opacity: 0;  transform: translate3d(0, 0, 0) rotate(0deg); }
    15%  { opacity: .75; }
    80%  { opacity: .4; }
    100% { opacity: 0;  transform: translate3d(var(--dx), -96px, 0) rotate(120deg); }
  }

  .pl-brand { margin-top: 10px; text-align: center; }
  .pl-title {
    font-size: clamp(20px, 4.6vw, 27px); font-weight: 700;
    letter-spacing: .42em; padding-left: .42em;
    background: linear-gradient(180deg, #0b1f4a 10%, #2a4f8f 100%);
    -webkit-background-clip: text; background-clip: text; color: transparent;
  }
  .pl-sub {
    margin-top: 9px; font-size: 10.5px; font-weight: 500;
    letter-spacing: .34em; padding-left: .34em; color: #6a7a92;
  }
  .pl-rule {
    width: 64px; height: 1px; margin: 18px 0 16px;
    background: linear-gradient(90deg, transparent, var(--pl-blue-soft), transparent); opacity: .8;
  }

  .pl-status {
    display: flex; align-items: center; gap: 2px;
    font-size: 12.5px; letter-spacing: .04em; color: #5a6a82;
    transition: opacity .12s linear;
  }
  .pl-label { transition: color .3s ease; }
  .pl-dots i {
    display: inline-block; width: 3px; height: 3px; margin-left: 3px; border-radius: 50%;
    background: currentColor; animation: pl-dot 1.2s ease-in-out infinite;
  }
  .pl-dots i:nth-child(2) { animation-delay: .15s; }
  .pl-dots i:nth-child(3) { animation-delay: .3s; }
  @keyframes pl-dot { 0%, 100% { opacity: .2; } 40% { opacity: 1; } }
  .pl-pct {
    margin-left: 12px; padding-left: 12px; min-width: 3.2ch; text-align: right;
    border-left: 1px solid rgba(20, 45, 90, .16);
    color: var(--pl-blue); font-variant-numeric: tabular-nums; font-weight: 600;
  }
  .pl-status.is-ready .pl-dots { display: none; }
  .pl-status.is-ready .pl-label { color: var(--pl-blue); }

  .pl-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

  @media (prefers-reduced-motion: reduce) {
    .pl-particles { display: none; }
    .pl-dots i { animation: none; opacity: .7; }
  }
</style>

<style id="pl-dark-theme">
  /* ---- Dark mode (follows the app's data-theme) ---- */
  [data-theme="dark"] .pl-loader {
    --pl-blue: #4aa3ff;
    --pl-blue-soft: #3a9bff;
    color: #dbe4f5;
    background:
      radial-gradient(52% 46% at 50% 40%, rgba(47, 155, 255, .16), transparent 72%),
      radial-gradient(120% 90% at 50% 0%, var(--surface, #1e212b) 0%, var(--paper, #15171c) 58%, #0e1015 100%);
  }
  [data-theme="dark"] .pl-loader::before {
    background-image: radial-gradient(rgba(150, 185, 245, .12) 1px, transparent 1.2px);
  }
  [data-theme="dark"] .pl-title { background-image: linear-gradient(180deg, #f2f6ff 10%, #8fb4f0 100%); }
  [data-theme="dark"] .pl-sub { color: #8d9bb3; }
  [data-theme="dark"] .pl-status { color: #a3b1c8; }
  [data-theme="dark"] .pl-pct { border-left-color: rgba(150, 185, 245, .25); }

  /* The printer artwork is light, so invert it, then undo the inversion
     on the parts whose real colours matter (logo, glows, nozzle, shadows). */
  [data-theme="dark"] .pl-stage svg { filter: invert(1) hue-rotate(180deg); }
  [data-theme="dark"] .pl-stage svg #plObj,
  [data-theme="dark"] .pl-stage svg #plNozGlow,
  [data-theme="dark"] .pl-stage svg #plTip,
  [data-theme="dark"] .pl-stage svg #plDot,
  [data-theme="dark"] .pl-stage svg #plFilHi,
  [data-theme="dark"] .pl-stage svg path[fill="url(#plGlass)"],
  [data-theme="dark"] .pl-stage svg path[d^="M172 68"],
  [data-theme="dark"] .pl-stage svg ellipse[fill="#1e3a6e"],
  [data-theme="dark"] .pl-stage svg ellipse[fill="#0b1f4a"] {
    filter: invert(1) hue-rotate(180deg);
  }
  /* Chamber colour (pre-inversion value, ends up close to the app's dark surface) */
  [data-theme="dark"] .pl-stage svg rect[fill="url(#plPanel)"] { fill: #dbdee8; }
  /* Softer glass reflections on dark */
  [data-theme="dark"] .pl-stage svg path[fill="url(#plGlass)"] { opacity: .35; }
  [data-theme="dark"] .pl-stage svg path[d^="M172 68"] { opacity: .12; }
</style>

<div class="pl-loader" id="pl-loader" role="status" aria-live="polite" data-autohide data-min="1200">
  <span class="pl-sr">Loading Pixel Lab</span>

  <div class="pl-stack">
    <div class="pl-stage">

      <svg viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <defs>
          <!-- brushed-aluminium / white-coat metals -->
          <linearGradient id="plPost" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#b3bfcf"/><stop offset=".28" stop-color="#ffffff"/>
            <stop offset=".55" stop-color="#dae1ec"/><stop offset="1" stop-color="#9fabbd"/>
          </linearGradient>
          <linearGradient id="plBeam" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#ffffff"/><stop offset=".5" stop-color="#e5ebf3"/><stop offset="1" stop-color="#b6c1d1"/>
          </linearGradient>
          <linearGradient id="plBase" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#ffffff"/><stop offset=".4" stop-color="#e3e9f2"/><stop offset="1" stop-color="#b4bfcf"/>
          </linearGradient>
          <linearGradient id="plRail" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#ffffff"/><stop offset=".45" stop-color="#c9d2df"/><stop offset="1" stop-color="#95a2b6"/>
          </linearGradient>
          <linearGradient id="plCar" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#c8d1de"/>
          </linearGradient>
          <linearGradient id="plHeat" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#cbd4e1"/><stop offset="1" stop-color="#8b98ad"/>
          </linearGradient>
          <linearGradient id="plBrass" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#8a5a1c"/><stop offset=".45" stop-color="#f3c777"/><stop offset="1" stop-color="#9a6a24"/>
          </linearGradient>

          <!-- panels / light -->
          <linearGradient id="plPanel" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#f7fafd"/><stop offset="1" stop-color="#e1e8f2"/>
          </linearGradient>
          <pattern id="plGrid" width="24" height="24" patternUnits="userSpaceOnUse">
            <path d="M24 0H0V24" fill="none" stroke="#1e3a6e" stroke-opacity=".07" stroke-width="1"/>
          </pattern>
          <linearGradient id="plLedG" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#2f9bff" stop-opacity="0"/><stop offset=".5" stop-color="#2f9bff"/><stop offset="1" stop-color="#2f9bff" stop-opacity="0"/>
          </linearGradient>
          <linearGradient id="plConeG" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#2f9bff" stop-opacity=".16"/><stop offset="1" stop-color="#2f9bff" stop-opacity=".01"/>
          </linearGradient>
          <radialGradient id="plGlowA">
            <stop offset="0" stop-color="#3aa0ff" stop-opacity=".34"/><stop offset=".55" stop-color="#3aa0ff" stop-opacity=".10"/><stop offset="1" stop-color="#3aa0ff" stop-opacity="0"/>
          </radialGradient>
          <radialGradient id="plNozGlowG">
            <stop offset="0" stop-color="#ffffff" stop-opacity=".95"/><stop offset=".25" stop-color="#7cc4ff" stop-opacity=".55"/><stop offset="1" stop-color="#2f9bff" stop-opacity="0"/>
          </radialGradient>
          <radialGradient id="plHalo">
            <stop offset="0" stop-color="#3aa0ff" stop-opacity=".5"/><stop offset="1" stop-color="#3aa0ff" stop-opacity="0"/>
          </radialGradient>
          <radialGradient id="plGroundG">
            <stop offset="0" stop-color="#3a86e0" stop-opacity=".34"/><stop offset="1" stop-color="#3a86e0" stop-opacity="0"/>
          </radialGradient>
          <radialGradient id="plBed" cx=".5" cy=".5" r=".5">
            <stop offset="0" stop-color="#ffffff"/><stop offset=".6" stop-color="#e4eaf2"/><stop offset="1" stop-color="#c7d1df"/>
          </radialGradient>
          <radialGradient id="plPoolG">
            <stop offset="0" stop-color="#3aa0ff" stop-opacity=".6"/><stop offset="1" stop-color="#3aa0ff" stop-opacity="0"/>
          </radialGradient>
          <linearGradient id="plGlass" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#fff" stop-opacity=".75"/><stop offset="1" stop-color="#fff" stop-opacity="0"/>
          </linearGradient>

          <!-- printed object -->
          <linearGradient id="plObjV" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity=".25"/><stop offset=".4" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#001033" stop-opacity=".28"/>
          </linearGradient>
          <pattern id="plLayers" x="0" y="138" width="10" height="8" patternUnits="userSpaceOnUse">
            <rect y="6" width="10" height="1" fill="#fff" fill-opacity=".22"/>
            <rect y="7" width="10" height="1" fill="#00102e" fill-opacity=".34"/>
          </pattern>
          <linearGradient id="plHotR" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#eaffff" stop-opacity="0"/><stop offset="1" stop-color="#eaffff" stop-opacity=".95"/>
          </linearGradient>
          <linearGradient id="plHotL" x1="1" x2="0" y1="0" y2="0">
            <stop offset="0" stop-color="#eaffff" stop-opacity="0"/><stop offset="1" stop-color="#eaffff" stop-opacity=".95"/>
          </linearGradient>
          <linearGradient id="plSheen" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".5" stop-color="#fff" stop-opacity=".7"/><stop offset="1" stop-color="#fff" stop-opacity="0"/>
          </linearGradient>

          <!-- filament spool -->
          <radialGradient id="plSpoolG" cx=".4" cy=".35" r=".8">
            <stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#c3cddb"/>
          </radialGradient>
          <linearGradient id="plFilG" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stop-color="#8fd6ff"/><stop offset=".5" stop-color="#1d8bff"/><stop offset="1" stop-color="#0a5fd6"/>
          </linearGradient>

          <!-- progress ring -->
          <linearGradient id="plRingG" gradientUnits="userSpaceOnUse" x1="24" y1="0" x2="376" y2="0">
            <stop offset="0" stop-color="#00b4ff"/><stop offset=".5" stop-color="#2563eb"/><stop offset="1" stop-color="#00b4ff"/>
          </linearGradient>

          <!-- the Pixel Lab logo: white silhouette (mask) + full-colour art -->
          <image id="plLogoSil" x="152.7" y="138" width="94.7" height="112" preserveAspectRatio="none" href="data:image/webp;base64,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"/>
          <mask id="plLogoMask" maskUnits="userSpaceOnUse" x="140" y="125" width="120" height="140"><use href="#plLogoSil"/></mask>
          <clipPath id="plRevealClip"><rect id="plRevA" x="140" y="250" width="120" height="0"/><rect id="plRevB" x="140" y="250" width="0" height="8"/></clipPath>
        </defs>

        <!-- ground light + orbit ring (perspective) -->
        <ellipse id="plGround" cx="200" cy="300" rx="158" ry="16" fill="url(#plGroundG)" opacity=".5"/>
        <g id="plRing" fill="none">
          <path d="M200 255 A176 27 0 1 1 200 309 A176 27 0 1 1 200 255" stroke="#1e3a6e" stroke-opacity=".12" stroke-width="1.5"/>
          <path d="M200 255 A176 27 0 1 1 200 309 A176 27 0 1 1 200 255" stroke="#1e3a6e" stroke-opacity=".22" stroke-width="3" stroke-dasharray="1 7"/>
          <path id="plArcGlow" d="M200 255 A176 27 0 1 1 200 309 A176 27 0 1 1 200 255" pathLength="100" stroke="url(#plRingG)" stroke-width="7" stroke-opacity=".2" stroke-linecap="round" stroke-dasharray="0 100"/>
          <path id="plArc" d="M200 255 A176 27 0 1 1 200 309 A176 27 0 1 1 200 255" pathLength="100" stroke="url(#plRingG)" stroke-width="2.2" stroke-linecap="round" stroke-dasharray="0 100"/>
          <circle id="plDotHalo" r="10" fill="url(#plHalo)" opacity="0"/>
          <circle id="plDot" r="3" fill="#ffffff" stroke="#1d6fe8" stroke-width="1.6" opacity="0"/>
        </g>

        <!-- base -->
        <ellipse cx="200" cy="291" rx="142" ry="8" fill="#1e3a6e" opacity=".22"/>
        <rect x="84" y="283" width="26" height="6" rx="2.5" fill="#a5b1c3"/>
        <rect x="290" y="283" width="26" height="6" rx="2.5" fill="#a5b1c3"/>
        <rect x="64" y="256" width="272" height="30" rx="11" fill="url(#plBase)" stroke="#1e3a6e" stroke-opacity=".14"/>
        <rect x="72" y="257" width="256" height="1.2" fill="#fff" fill-opacity=".95"/>
        <rect x="168" y="264" width="64" height="14" rx="4" fill="#dde5ef" stroke="#1e3a6e" stroke-opacity=".14"/>
        <text x="200" y="273.4" text-anchor="middle" font-size="4.8" letter-spacing="1.5" font-weight="700" fill="#5b6b82" font-family="system-ui, sans-serif">PIXEL LAB</text>
        <circle id="plLed" cx="173.5" cy="271" r="1.4" fill="#2f9bff"/>
        <circle cx="226.5" cy="271" r="1.4" fill="#aab6c8"/>

        <!-- enclosure interior -->
        <rect x="92" y="66" width="216" height="192" fill="url(#plPanel)"/>
        <rect x="92" y="66" width="216" height="192" fill="url(#plGrid)"/>
        <polygon id="plCone" points="110,68 290,68 264,250 136,250" fill="url(#plConeG)" opacity="0"/>
        <circle id="plGlowArea" cx="200" cy="198" r="108" fill="url(#plGlowA)" opacity=".1"/>

        <!-- posts -->
        <g>
          <rect x="78" y="60" width="14" height="198" fill="url(#plPost)"/>
          <rect x="83" y="66" width="4" height="190" fill="#5f6d83" opacity=".3"/>
          <rect x="78.4" y="60" width="0.9" height="198" fill="#fff" opacity=".9"/>
          <rect x="308" y="60" width="14" height="198" fill="url(#plPost)"/>
          <rect x="313" y="66" width="4" height="190" fill="#5f6d83" opacity=".3"/>
          <rect x="308.4" y="60" width="0.9" height="198" fill="#fff" opacity=".9"/>
        </g>

        <!-- top beam + LED strip -->
        <rect x="70" y="46" width="260" height="20" rx="7" fill="url(#plBeam)" stroke="#1e3a6e" stroke-opacity=".14"/>
        <rect x="80" y="47.2" width="240" height="1.2" fill="#fff"/>
        <rect id="plLedStrip" x="96" y="66" width="208" height="2.6" fill="url(#plLedG)" opacity=".15"/>
        <circle cx="87" cy="56" r="1.9" fill="#2f9bff"/>
        <circle cx="95" cy="56" r="1.9" fill="#f5a623"/>

        <!-- filament spool -->
        <g transform="translate(266 26)">
          <circle r="20" fill="url(#plSpoolG)" stroke="#8f9cb1" stroke-width=".8"/>
          <circle r="15.5" fill="url(#plFilG)"/>
          <circle r="15.5" fill="none" stroke="#001a4d" stroke-opacity=".22" stroke-width="1" stroke-dasharray="1.2 2.4"/>
          <circle r="8.2" fill="#e8eef6"/>
          <g id="plSpoolRot">
            <line x1="0" y1="-3" x2="0" y2="-7.4" stroke="#5b6a80" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="3" y1="0" x2="7.4" y2="0" stroke="#5b6a80" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="0" y1="3" x2="0" y2="7.4" stroke="#5b6a80" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="-3" y1="0" x2="-7.4" y2="0" stroke="#5b6a80" stroke-width="1.4" stroke-linecap="round"/>
          </g>
          <circle r="2.2" fill="#8a97ab"/>
        </g>

        <!-- print bed (perspective) -->
        <ellipse cx="200" cy="254.5" rx="102" ry="9.5" fill="#a9b5c6"/>
        <ellipse cx="200" cy="251" rx="102" ry="9.5" fill="#f2f6fb" stroke="#1e3a6e" stroke-opacity=".14"/>
        <ellipse cx="200" cy="251" rx="97" ry="7.4" fill="url(#plBed)"/>
        <ellipse id="plPool" cx="200" cy="251" rx="58" ry="5.6" fill="url(#plPoolG)" opacity=".2"/>
        <ellipse cx="200" cy="250.6" rx="46" ry="3" fill="#0b1f4a" opacity=".3"/>

        <!-- printed object: the Pixel Lab logo, revealed layer by layer -->
        <g id="plObj">
          <circle id="plEmbHalo" cx="200" cy="194" r="72" fill="url(#plHalo)" opacity="0"/>
          <g clip-path="url(#plRevealClip)">
            <image id="plLogoColor" x="152.7" y="138" width="94.7" height="112" preserveAspectRatio="none" href="data:image/webp;base64,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"/>
            <g mask="url(#plLogoMask)">
              <rect id="plLayerLines" x="140" y="135" width="120" height="118" fill="url(#plLayers)"/>
              <rect x="140" y="135" width="120" height="118" fill="url(#plObjV)" opacity=".6"/>
              <rect id="plHot" x="0" y="250" width="26" height="8" fill="url(#plHotR)" opacity="0"/>
              <rect id="plSheenBar" x="-14" y="125" width="28" height="140" fill="url(#plSheen)" opacity="0" transform="translate(215 0) skewX(-20)"/>
            </g>
          </g>
          <!-- brief lift once the print completes -->
          <g id="plEmblem" opacity="0"><use href="#plLogoSil" opacity=".38"/></g>
        </g>

        <!-- gantry (rail rises with each layer) -->
        <g id="plGantry" transform="translate(0 171)">
          <rect x="96" y="8" width="208" height="5" fill="#1e3a6e" opacity=".14"/>
          <rect x="96" y="0" width="208" height="8" rx="2" fill="url(#plRail)" stroke="#1e3a6e" stroke-opacity=".12"/>
          <rect x="96" y="0.6" width="208" height="0.9" fill="#fff"/>
          <rect x="74" y="-7" width="22" height="22" rx="4" fill="url(#plCar)" stroke="#1e3a6e" stroke-opacity=".18"/>
          <rect x="304" y="-7" width="22" height="22" rx="4" fill="url(#plCar)" stroke="#1e3a6e" stroke-opacity=".18"/>
          <circle cx="80" cy="-2" r="1.3" fill="#8592a6"/><circle cx="80" cy="10" r="1.3" fill="#8592a6"/>
          <circle cx="320" cy="-2" r="1.3" fill="#8592a6"/><circle cx="320" cy="10" r="1.3" fill="#8592a6"/>
        </g>

        <!-- filament: shadow, body, travelling highlight -->
        <path id="plFilSh" d="M252 40 C250 90 200 90 200 150" fill="none" stroke="#1e3a6e" stroke-opacity=".2" stroke-width="3.8" stroke-linecap="round"/>
        <path id="plFil" d="M252 40 C250 90 200 90 200 150" fill="none" stroke="#1d8bff" stroke-width="2.2" stroke-linecap="round"/>
        <path id="plFilHi" d="M252 40 C250 90 200 90 200 150" fill="none" stroke="#d9f1ff" stroke-width="1.3" stroke-linecap="round" stroke-dasharray="5 15" opacity="0"/>

        <!-- print head (origin = nozzle tip) -->
        <g id="plHead" transform="translate(284 213)">
          <circle id="plNozGlow" cx="0" cy="2" r="26" fill="url(#plNozGlowG)" opacity="0"/>
          <rect x="-19" y="-50" width="38" height="22" rx="5" fill="url(#plCar)" stroke="#1e3a6e" stroke-opacity=".2"/>
          <rect x="-16" y="-49" width="32" height="1.2" fill="#fff"/>
          <path d="M19 -46 h7 a3 3 0 0 1 3 3 v14 a3 3 0 0 1 -3 3 h-7z" fill="#dbe3ee" stroke="#1e3a6e" stroke-opacity=".2"/>
          <circle cx="0" cy="-39" r="7" fill="#f1f5fa" stroke="#9eabbe" stroke-width=".8"/>
          <g id="plFan">
            <ellipse cx="0" cy="-43.4" rx="1.6" ry="3" fill="#6f7d93"/>
            <ellipse cx="0" cy="-43.4" rx="1.6" ry="3" fill="#6f7d93" transform="rotate(120 0 -39)"/>
            <ellipse cx="0" cy="-43.4" rx="1.6" ry="3" fill="#6f7d93" transform="rotate(240 0 -39)"/>
          </g>
          <circle cx="0" cy="-39" r="1.3" fill="#3a475c"/>
          <circle cx="-13.5" cy="-45" r="1.5" fill="#2f9bff"/>
          <rect x="-9" y="-30" width="18" height="13" fill="#d2dae6"/>
          <g stroke="#7d8aa0" stroke-width=".9"><line x1="-5.5" y1="-30" x2="-5.5" y2="-17"/><line x1="-1.8" y1="-30" x2="-1.8" y2="-17"/><line x1="1.8" y1="-30" x2="1.8" y2="-17"/><line x1="5.5" y1="-30" x2="5.5" y2="-17"/></g>
          <rect x="-8" y="-17" width="16" height="9" rx="1.5" fill="url(#plHeat)"/>
          <rect id="plHeatLine" x="-8" y="-13.6" width="16" height="1.3" fill="#ff9d2e" opacity="0"/>
          <path d="M-4.5 -8 H4.5 L1.7 0 H-1.7Z" fill="url(#plBrass)"/>
          <circle id="plTip" cx="0" cy=".6" r="1.7" fill="#ffffff" opacity="0"/>
        </g>

        <!-- enclosure glass -->
        <path d="M104 68 L158 68 L122 256 L104 256Z" fill="url(#plGlass)" opacity=".7"/>
        <path d="M172 68 L180 68 L146 256 L138 256Z" fill="#fff" opacity=".26"/>
      </svg>

      <div class="pl-particles" id="plParticles" aria-hidden="true"></div>
    </div>

    <div class="pl-brand" aria-hidden="true">
      <div class="pl-title">PIXEL LAB</div>
      <div class="pl-sub">3D PRINTING MANAGEMENT SYSTEM</div>
    </div>
    <div class="pl-rule" aria-hidden="true"></div>

    <div class="pl-status" id="plStatus" aria-hidden="true">
      <span class="pl-label" id="plLabel">Initializing Pixel Lab</span>
      <span class="pl-dots"><i></i><i></i><i></i></span>
      <span class="pl-pct" id="plPct">0%</span>
    </div>
  </div>
</div>

<script>
(() => {
  const $ = id => document.getElementById(id);
  const el = $('pl-loader');

  /* ---------- timeline (seconds) ---------- */
  const T = 4.0;                     // loop length
  const N = 14, LH = 8, BED = 250;   // layers, layer height, bed surface y
  const X0 = 145, X1 = 255, REST = 284;
  const Z_HOME = 36, Z_TOP = N * LH, Z_END = 132;
  //  0.00–0.45 prepare · 0.45–0.85 move into position · 0.85–3.00 print
  //  3.00–3.50 finish + logo reveal · 3.50–3.65 hold · 3.65–4.00 reset

  const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
  const lerp  = (a, b, t) => a + (b - a) * t;
  const eio   = t => t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
  const eout  = t => 1 - Math.pow(1 - t, 3);
  const esine = t => -(Math.cos(Math.PI * t) - 1) / 2;
  const f1 = n => n.toFixed(1);

  const ref = {};
  ['plRevA','plRevB','plHot','plSheenBar','plObj','plEmblem','plEmbHalo','plGlowArea','plHead','plNozGlow',
   'plTip','plHeatLine','plLayerLines','plGantry','plFil','plFilSh','plFilHi','plSpoolRot','plFan','plLed','plLedStrip',
   'plCone','plPool','plArc','plArcGlow','plDot','plDotHalo','plGround','plStatus','plLabel','plPct']
    .forEach(id => ref[id] = $(id));
  const arcLen = ref.plArc.getTotalLength();

  function progress(t) {
    if (t < .85) return .08 * eio(t / .85);
    if (t < 3.0) return .08 + .84 * (t - .85) / 2.15;
    if (t < 3.5) return .92 + .08 * eio((t - 3) / .5);
    if (t < 3.9) return 1;
    return 0;
  }

  let lastLabel = '', lastPct = -1, lastReady = null;

  function frame(t) {
    /* -- kinematics -- */
    let x = REST, z = Z_HOME, s = -1, k = 0, u = 0, right = true;
    if (t < .45)       { z = lerp(Z_HOME, 0, eio(t / .45)); }
    else if (t < .85)  { z = 0; x = lerp(REST, X0, eio((t - .45) / .4)); }
    else if (t < 3.0)  {
      s = (t - .85) / 2.15; z = Z_TOP * s;
      k = Math.min(N - 1, Math.floor(s * N)); u = s * N - k; right = k % 2 === 0;
      const e = esine(clamp(u)); x = right ? lerp(X0, X1, e) : lerp(X1, X0, e);
    }
    else if (t < 3.5)  { const f = (t - 3) / .5; s = 2; z = Z_TOP + (Z_END - Z_TOP) * eout(f); x = lerp(X0, REST, eio(f)); }
    else if (t < 3.65) { s = 2; z = Z_END; x = REST; }
    else               { s = 2; z = lerp(Z_END, Z_HOME, eio((t - 3.65) / .35)); x = REST; }

    const heat = t < .45 ? esine(t / .45) : t < 3.5 ? 1 : 1 - clamp((t - 3.5) / .5);
    const ext  = clamp((t - .8) / .1) * (1 - clamp((t - 2.95) / .1));
    const em   = eout(clamp((t - 3.0) / .5));
    const objOp = t < 3.65 ? 1 : 1 - clamp((t - 3.65) / .3);
    const jit  = ext * Math.sin(t * 140) * .18;
    const tipY = BED - 1 - z;

    /* -- layer reveal -- */
    let aY = BED, aH = 0, bX = 140, bW = 0, bY = BED;
    if (s >= 0 && s <= 1) {
      aY = BED - LH * k; aH = LH * k + 3; bY = BED - LH * (k + 1);
      if (right) { bX = 140; bW = clamp(x - 140, 0, 120); } else { bX = x; bW = clamp(260 - x, 0, 120); }
    } else if (s > 1) { aY = 125; aH = 140; }
    ref.plRevA.setAttribute('y', aY); ref.plRevA.setAttribute('height', aH);
    ref.plRevB.setAttribute('x', bX); ref.plRevB.setAttribute('y', bY); ref.plRevB.setAttribute('width', bW);

    /* freshly extruded bead behind the nozzle */
    const hot = ref.plHot;
    hot.setAttribute('x', right ? x - 26 : x); hot.setAttribute('y', bY);
    hot.setAttribute('fill', right ? 'url(#plHotR)' : 'url(#plHotL)');
    hot.setAttribute('opacity', s >= 0 && s <= 1 ? ext : 0);

    /* logo sheen sweep */
    const sf = (t - 3.05) / .5;
    ref.plSheenBar.setAttribute('opacity', sf > 0 && sf < 1 ? .9 : 0);
    ref.plSheenBar.setAttribute('transform', `translate(${f1(lerp(208, 342, esine(clamp(sf))))} 0) skewX(-20)`);

    ref.plObj.setAttribute('opacity', objOp);
    const flash = Math.sin(Math.PI * clamp((t - 3.0) / .5));          // brief lift as the logo completes
    ref.plEmblem.setAttribute('opacity', em * (.35 + .65 * flash));
    ref.plEmbHalo.setAttribute('opacity', em * (.55 + .45 * Math.sin(Math.max(0, t - 3.0) * 5)));
    ref.plLayerLines.setAttribute('opacity', 1 - .6 * em);           // layer lines soften once finished

    /* -- machine -- */
    ref.plHead.setAttribute('transform', `translate(${f1(x)} ${f1(tipY + jit)})`);
    ref.plGantry.setAttribute('transform', `translate(0 ${f1(tipY - 42)})`);
    ref.plFan.setAttribute('transform', `rotate(${f1(t * 270 % 360)} 0 -39)`);
    ref.plSpoolRot.setAttribute('transform', `rotate(${f1(-clamp(t - .85, 0, 2.15) * (270 / 2.15))})`);
    ref.plNozGlow.setAttribute('opacity', .15 * heat + .75 * ext);
    ref.plTip.setAttribute('opacity', .2 * heat + .8 * ext);
    ref.plHeatLine.setAttribute('opacity', heat);
    ref.plLed.setAttribute('opacity', .55 + .45 * Math.sin(t * 9));

    /* filament path spool → head */
    const sx = 252, sy = 40, ex = x, ey = tipY + jit - 48, dy = ey - sy;
    const d = `M${sx} ${sy} C${f1(sx - 8)} ${f1(sy + dy * .55)} ${f1(ex)} ${f1(ey - dy * .5)} ${f1(ex)} ${f1(ey)}`;
    ref.plFil.setAttribute('d', d); ref.plFilSh.setAttribute('d', d); ref.plFilHi.setAttribute('d', d);
    ref.plFilHi.setAttribute('stroke-dashoffset', f1(-t * 70));
    ref.plFilHi.setAttribute('opacity', .12 * heat + .88 * ext);

    /* -- studio light -- */
    ref.plLedStrip.setAttribute('opacity', .15 + .85 * heat);
    ref.plCone.setAttribute('opacity', .75 * heat);
    ref.plGlowArea.setAttribute('opacity', (.1 + .3 * heat + .25 * em + .08 * Math.sin(t * 6)) * (t > 3.65 ? objOp : 1));
    ref.plPool.setAttribute('opacity', (.2 + .55 * heat + .15 * em) * (t > 3.65 ? objOp : 1));
    ref.plGround.setAttribute('opacity', .35 + .35 * heat);

    /* -- progress ring -- */
    const p = progress(t);
    const arcOp = t < .25 ? t / .25 : t > 3.6 ? clamp(1 - (t - 3.6) / .3) : 1;
    const pulse = t > 3.3 && t < 3.8 ? Math.sin(Math.PI * (t - 3.3) / .5) : 0;
    const dash = `${(p * 100).toFixed(2)} 100`;
    ref.plArc.setAttribute('stroke-dasharray', dash);
    ref.plArcGlow.setAttribute('stroke-dasharray', dash);
    ref.plArc.setAttribute('opacity', p > .002 ? arcOp : 0);
    ref.plArcGlow.setAttribute('stroke-opacity', (.2 + .5 * pulse) * arcOp);
    const pt = ref.plArc.getPointAtLength(p * arcLen);
    const dotOp = p > .002 ? arcOp : 0;
    ref.plDot.setAttribute('cx', f1(pt.x)); ref.plDot.setAttribute('cy', f1(pt.y));
    ref.plDotHalo.setAttribute('cx', f1(pt.x)); ref.plDotHalo.setAttribute('cy', f1(pt.y));
    ref.plDot.setAttribute('opacity', dotOp); ref.plDotHalo.setAttribute('opacity', dotOp * (.8 + .6 * pulse));

    /* -- status text -- */
    const label = (t < .85 || t >= 3.85) ? 'Initializing Pixel Lab'
                : t < 3.3 ? 'Printing your workspace' : 'Workspace ready';
    const ready = label === 'Workspace ready';
    const pct = Math.round(p * 100);
    if (label !== lastLabel) { ref.plLabel.textContent = label; lastLabel = label; }
    if (pct !== lastPct)     { ref.plPct.textContent = pct + '%'; lastPct = pct; }
    if (ready !== lastReady) { ref.plStatus.classList.toggle('is-ready', ready); lastReady = ready; }
    ref.plStatus.style.opacity = t < 3.7 ? 1 : t < 3.85 ? 1 - (t - 3.7) / .15 : clamp((t - 3.85) / .15);
  }

  /* ---------- floating data particles (seeded, deterministic) ---------- */
  (function particles() {
    const host = $('plParticles');
    let a = 0x9e3779b9;
    const rnd = () => { a |= 0; a = a + 0x6D2B79F5 | 0; let t = Math.imul(a ^ a >>> 15, 1 | a); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; };
    for (let i = 0; i < 22; i++) {
      const p = document.createElement('i');
      p.style.setProperty('--x', (22 + rnd() * 56).toFixed(1) + '%');
      p.style.setProperty('--y', (34 + rnd() * 46).toFixed(1) + '%');
      p.style.setProperty('--s', (2 + rnd() * 2.4).toFixed(1) + 'px');
      p.style.setProperty('--d', (3.2 + rnd() * 3).toFixed(2) + 's');
      p.style.setProperty('--dl', (-rnd() * 6).toFixed(2) + 's');
      p.style.setProperty('--dx', ((rnd() - .5) * 46).toFixed(0) + 'px');
      host.appendChild(p);
    }
  })();

  /* ---------- run loop ---------- */
  const reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  let raf = 0, t0 = 0;
  const loop = now => { frame(((now - t0) / 1000) % T); raf = requestAnimationFrame(loop); };
  const start = () => { cancelAnimationFrame(raf); if (reduce) { frame(2.2); return; } t0 = performance.now(); raf = requestAnimationFrame(loop); };
  const stop  = () => cancelAnimationFrame(raf);
  frame(0); start();

  /* ---------- public API ---------- */
  let hideTimer = 0;
  window.PixelLabLoader = {
    show() { clearTimeout(hideTimer); el.classList.remove('is-hidden'); start(); },
    hide() { el.classList.add('is-hidden'); hideTimer = setTimeout(stop, 700); }
  };

  if (el.hasAttribute('data-autohide')) {
    const min = window.PL_LOADER_MIN || (+el.dataset.min || 1600);  // minimum on-screen time (ms)
    addEventListener('DOMContentLoaded', () => setTimeout(() => window.PixelLabLoader.hide(), Math.max(0, min - performance.now())));
}
})();
</script>
@endverbatim