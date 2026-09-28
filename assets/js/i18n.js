(function () {
  var D = {
    en: {nav_scan:'Scan',nav_vehicles:'Vehicles',nav_manual:'Manual',scan_title:'License Plate Scanner',start_cam:'Start camera',capture:'Capture & scan',drop:'Drop a plate image here or click to upload',result:'Scan result',confidence:'Confidence',
      reg_title:'Vehicle Registration',plate:'License plate',owner:'Owner full name',phone:'Phone number',make:'Make / Brand',model:'Model',color:'Color',body:'Body type',currency:'Currency',choose:'Choose…',
      fee:'Registration fee',submit:'Register vehicle',scanning:'Scanning…',registered:'Vehicle registered. Fee:',rates_stale:'Live rates unavailable; using older cached rates.',
      err_network:'Network error. Please try again.',err_cam:'Camera unavailable. Check browser permissions (HTTPS required) or upload an image.',
      search:'Search plate, owner, make or model',all_types:'All types',from:'From',to:'To',th_plate:'Plate',th_owner:'Owner',th_vehicle:'Vehicle',th_type:'Type',th_fee:'Fee paid',th_date:'Registered',
      none:'No vehicles found.',prev:'Previous',next:'Next',page:'Page',guide:'Align the plate inside the guide box'},
    zh: {nav_scan:'扫描',nav_vehicles:'车辆',nav_manual:'手册',scan_title:'车牌扫描器',start_cam:'开启摄像头',capture:'拍摄并扫描',drop:'将车牌图片拖到此处或点击上传',result:'扫描结果',confidence:'置信度',
      reg_title:'车辆登记',plate:'车牌号码',owner:'车主姓名',phone:'电话号码',make:'品牌',model:'型号',color:'颜色',body:'车身类型',currency:'货币',choose:'请选择…',
      fee:'登记费用',submit:'登记车辆',scanning:'扫描中…',registered:'车辆已登记。费用：',rates_stale:'无法获取实时汇率，使用旧的缓存汇率。',
      err_network:'网络错误，请重试。',err_cam:'无法使用摄像头。请检查浏览器权限（需要 HTTPS）或上传图片。',
      search:'搜索车牌、车主、品牌或型号',all_types:'所有类型',from:'起始',to:'截止',th_plate:'车牌',th_owner:'车主',th_vehicle:'车辆',th_type:'类型',th_fee:'已付费用',th_date:'登记日期',
      none:'未找到车辆。',prev:'上一页',next:'下一页',page:'页',guide:'请将车牌对准框内'}
  };
  var lang = 'en';
  try { lang = localStorage.getItem('lang') || (navigator.language || '').indexOf('zh') === 0 ? 'zh' : 'en'; lang = localStorage.getItem('lang') || lang; } catch (e) {}
  window.t = function (k) { return (D[lang] && D[lang][k]) || D.en[k] || k; };
  window.i18n = {
    apply: function () {
      document.documentElement.lang = lang;
      document.querySelectorAll('[data-i18n]').forEach(function (n) { n.textContent = t(n.getAttribute('data-i18n')); });
      document.querySelectorAll('[data-i18n-ph]').forEach(function (n) { n.placeholder = t(n.getAttribute('data-i18n-ph')); });
      document.querySelectorAll('[data-lang-block]').forEach(function (n) { n.hidden = n.getAttribute('data-lang-block') !== lang; });
      var s = document.getElementById('lang'); if (s) s.value = lang;
    },
    set: function (l) { lang = l; try { localStorage.setItem('lang', l); } catch (e) {} this.apply(); }
  };
  document.addEventListener('DOMContentLoaded', function () {
    i18n.apply();
    var s = document.getElementById('lang'); if (s) s.addEventListener('change', function () { i18n.set(s.value); });
  });
})();
