<?php
// student_certificate_template.php - Print Template
$license_type = getLicenseType();
?>
<div class="print-area" id="printArea">
    <img src="../assets/img/logo.png" alt="School Logo" class="school-logo">
    <!-- Certificate content here -->
</div>
<div style="text-align:center; margin:2em; display:flex; gap:1em; justify-content:center; align-items:center;">
    <?php if ($license_type === 'pro'): ?>
        <button id="savePngBtn" style="background:#0090d0;color:#fff;position:relative;">Save as PNG <span class="pro-badge" style="background:#b48e00;color:#fff;font-size:0.85em;margin-left:0.4em;">Pro</span></button>
    <?php else: ?>
        <button disabled title="Pro feature" style="background:#eee;color:#bbb;cursor:not-allowed;position:relative;">Save as PNG <span class="pro-badge" style="background:#b48e00;color:#fff;font-size:0.85em;margin-left:0.4em;">Pro</span> <span style="margin-left:0.3em;">🔒</span></button>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('savePngBtn');
    if (btn) {
        btn.addEventListener('click', function() {
            var card = document.getElementById('printArea');
            if (!card) return;
            html2canvas(card, {backgroundColor: '#fff', scale: 2}).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'student-certificate.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });
    }
});
</script>