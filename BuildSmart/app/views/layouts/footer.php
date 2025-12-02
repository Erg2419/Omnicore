  </section>

  <footer class="bg-white text-center text-gray-500 text-sm py-4 shadow-inner">
    © <?= date('Y') ?> <span class="font-semibold text-[#f97316]">BuildSmart</span> — Innovación en Construcción
  </footer>
</main>

<script>
  const toggleBtn = document.getElementById('toggleSidebar');
  const sidebar = document.getElementById('sidebar');

  toggleBtn?.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
  });
</script>

</body>
</html>
