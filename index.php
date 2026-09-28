<?php require_once __DIR__ . '/includes/layout.php'; page_head('Scanner', 'scan'); ?>
<div class="grid">
  <section class="card">
    <h1 data-i18n="scan_title">License Plate Scanner</h1>
    <div class="cam">
      <video id="video" autoplay playsinline muted hidden></video>
      <canvas id="canvas" hidden></canvas>
      <div class="guide" id="guide" hidden><span data-i18n="guide">Align the plate inside the guide box</span></div>
    </div>
    <div class="row">
      <button id="startCam" type="button" class="sec" data-i18n="start_cam">Start camera</button>
      <button id="capture" type="button" disabled data-i18n="capture">Capture &amp; scan</button>
    </div>
    <div class="drop" id="drop" data-i18n="drop">Drop a plate image here or click to upload</div>
    <input type="file" id="file" accept="image/jpeg,image/png,image/webp" hidden>
    <div id="scanMsg" class="msg" hidden></div>
    <div id="result" hidden><h3 data-i18n="result">Scan result</h3>
      <div class="res"><span class="plate" id="resPlate"></span><span><span data-i18n="confidence">Confidence</span>: <b id="resConf"></b></span></div></div>
  </section>
  <section class="card">
    <h1 data-i18n="reg_title">Vehicle Registration</h1>
    <form id="regForm" class="form" novalidate autocomplete="off">
      <div class="full"><label for="plate" data-i18n="plate">License plate</label><input id="plate" name="plate_number" maxlength="20" required><div class="err" data-err="plate_number"></div></div>
      <div class="full"><label data-i18n="owner">Owner full name</label><input name="owner_name" maxlength="100" required><div class="err" data-err="owner_name"></div></div>
      <div><label data-i18n="phone">Phone number</label><input name="phone" type="tel" maxlength="20" required><div class="err" data-err="phone"></div></div>
      <div><label data-i18n="color">Color</label><input name="color" maxlength="30" required><div class="err" data-err="color"></div></div>
      <div><label data-i18n="make">Make / Brand</label><input name="make" maxlength="50" placeholder="Toyota, Honda, Proton" required><div class="err" data-err="make"></div></div>
      <div><label data-i18n="model">Model</label><input name="model" maxlength="50" placeholder="Corolla, Civic, Saga" required><div class="err" data-err="model"></div></div>
      <div><label data-i18n="body">Body type</label><select name="body_type" required><option value="" data-i18n="choose">Choose…</option>
        <?php foreach (BODY_TYPES as $b): ?><option><?= e($b) ?></option><?php endforeach; ?></select><div class="err" data-err="body_type"></div></div>
      <div><label data-i18n="currency">Currency</label><select name="currency" id="currency" required>
        <?php foreach (CURRENCIES as $c): ?><option <?= $c === 'USD' ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><div class="err" data-err="currency"></div></div>
      <div class="full"><label data-i18n="fee">Registration fee</label><div class="fee" id="feeOut">—</div></div>
      <div class="full"><button type="submit" data-i18n="submit">Register vehicle</button></div>
    </form>
    <div id="regMsg" class="msg" hidden></div>
  </section>
</div>
<?php page_foot(); ?>
