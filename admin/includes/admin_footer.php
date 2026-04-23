  </div><!-- /a-content -->
</div><!-- /a-main -->

<script>
// Auto-hide alerts
document.querySelectorAll('.alert').forEach(a => {
  setTimeout(() => { a.style.transition='all .5s'; a.style.opacity='0'; a.style.maxHeight='0'; a.style.overflow='hidden'; }, 4500);
});
// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', e => { if (!confirm(btn.dataset.confirm)) e.preventDefault(); });
});
</script>
</body>
</html>
