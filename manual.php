<?php require_once __DIR__ . '/includes/layout.php'; page_head('Manual', 'manual'); ?>
<section class="card" data-lang-block="en">
  <h1>User manual</h1>
  <h3>Register a vehicle</h3>
  <p>On <b>Scan</b>, start the camera (HTTPS required) and press <i>Capture &amp; scan</i>, or drop a plate photo. The detected plate is boxed in green and auto-filled; you can edit it. Fill in the remaining fields, choose a currency (the fee updates live), then submit.</p>
  <h3>Tips</h3><p>Fill the guide box with the plate, avoid glare, and keep the camera steady.</p>
  <h3>API</h3>
  <p>All endpoints return JSON <code>{"status":"success|error", ...}</code>. Limit: 30 requests/minute/IP. POST endpoints need header <code>X-CSRF-Token</code> (from the page's <code>csrf</code> meta tag).</p>
  <pre>POST api/scan.php      multipart: image (jpg/png/webp, ≤4MB)
  → {"status":"success","data":{"plate_number":"ABC1234","confidence":0.92,"bbox":{...}}}
  → errors: NO_PLATE_DETECTED, PLATE_UNREADABLE, SCAN_UNAVAILABLE
POST api/register.php  JSON: plate_number, owner_name, phone, make, model, color, body_type, currency
GET  api/rates.php     → USD-based rates, source, fetched_at (1h cache)
GET  api/vehicles.php  ?q=&amp;type=&amp;from=YYYY-MM-DD&amp;to=YYYY-MM-DD&amp;page=1</pre>
</section>
<section class="card" data-lang-block="zh" hidden>
  <h1>使用手册</h1>
  <h3>登记车辆</h3>
  <p>在“扫描”页开启摄像头（需要 HTTPS）并点击“拍摄并扫描”，或拖入车牌照片。识别到的车牌会以绿框标出并自动填入，可手动修改。填写其余信息、选择货币（费用即时更新）后提交。</p>
  <h3>提示</h3><p>让车牌充满引导框，避免反光，保持相机稳定。</p>
  <h3>API</h3>
  <p>所有接口返回 JSON。每个 IP 每分钟最多 30 次请求。POST 接口需要请求头 <code>X-CSRF-Token</code>。接口列表见英文版。</p>
</section>
<?php page_foot(); ?>
