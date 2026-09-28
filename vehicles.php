<?php require_once __DIR__ . '/includes/layout.php'; page_head('Vehicles', 'vehicles'); ?>
<section class="card">
  <h1 data-i18n="nav_vehicles">Vehicles</h1>
  <div class="filters">
    <input id="q" type="search" maxlength="60" data-i18n-ph="search" placeholder="Search plate, owner, make or model">
    <select id="type"><option value="" data-i18n="all_types">All types</option><?php foreach (BODY_TYPES as $b): ?><option><?= e($b) ?></option><?php endforeach; ?></select>
    <input id="from" type="date" aria-label="From"><input id="to" type="date" aria-label="To">
  </div>
  <div id="vMsg" class="msg" hidden></div>
  <div class="tw"><table id="vtable"><thead><tr><th data-i18n="th_plate">Plate</th><th data-i18n="th_owner">Owner</th><th data-i18n="th_vehicle">Vehicle</th><th data-i18n="th_type">Type</th><th data-i18n="th_fee">Fee paid</th><th data-i18n="th_date">Registered</th></tr></thead><tbody></tbody></table></div>
  <div class="pager"><button id="prev" class="sec" data-i18n="prev">Previous</button><span id="pageInfo"></span><button id="next" class="sec" data-i18n="next">Next</button></div>
</section>
<?php page_foot(); ?>
