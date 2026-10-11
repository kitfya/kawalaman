    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                
                <!-- Col 1: Branding -->
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <span class="text-xl font-bold text-white"><?= APP_NAME ?></span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Platform edukasi dan pengaduan perundungan (bullying) terpadu. Kami berkomitmen menciptakan lingkungan belajar dan bersosialisasi yang aman, suportif, dan bebas intimidasi.
                    </p>
                    <div class="flex items-center gap-2 text-xs text-emerald-400 bg-emerald-950/60 border border-emerald-800/80 px-3 py-1.5 rounded-lg w-fit">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Kerahasiaan Identitas Pelapor Dijamin 100%
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Navigasi Utama</h4>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li><a href="index.php#edukasi" class="hover:text-indigo-400 transition-colors">Apa itu Bullying?</a></li>
                        <li><a href="index.php#jenis" class="hover:text-indigo-400 transition-colors">4 Bentuk Perundungan</a></li>
                        <li><a href="index.php#alur" class="hover:text-indigo-400 transition-colors">SOP & Alur Penanganan</a></li>
                        <li><a href="cek-status.php" class="hover:text-indigo-400 transition-colors font-medium text-slate-300">Pantau Status Laporan</a></li>
                        <li><a href="#" class="btn-open-report hover:text-rose-400 transition-colors font-semibold text-rose-400">Kirim Laporan Kasus</a></li>
                    </ul>
                </div>

                <!-- Col 3: Layanan Darurat Nasional -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Saluran Darurat Bebas Pulsa</h4>
                    <ul class="space-y-3 text-xs text-slate-400">
                        <li class="flex items-start gap-2.5">
                            <span class="px-2 py-0.5 rounded bg-rose-950 text-rose-400 font-bold border border-rose-800 text-[11px]">129</span>
                            <div>
                                <span class="font-semibold text-slate-200 block">SAPA 129 (KemenPPPA)</span>
                                <span>Layanan Perlindungan Perempuan & Anak</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="px-2 py-0.5 rounded bg-blue-950 text-blue-400 font-bold border border-blue-800 text-[11px]">110</span>
                            <div>
                                <span class="font-semibold text-slate-200 block">Polri Presisi</span>
                                <span>Panggilan Darurat Kepolisian RI</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <span class="px-2 py-0.5 rounded bg-amber-950 text-amber-400 font-bold border border-amber-800 text-[11px]">119</span>
                            <div>
                                <span class="font-semibold text-slate-200 block">Sejiwa (Kemenkes)</span>
                                <span>Layanan Konseling Psikologis Darurat</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Tim Satgas Internal -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Sekretariat Satgas</h4>
                    <p class="text-xs text-slate-400 mb-3 leading-relaxed">
                        Ruang Satgas Pencegahan dan Penanganan Kekerasan & Perundungan.<br>
                        Senin - Jumat, 08:00 - 16:00 WIB
                    </p>
                    <div class="space-y-2 text-xs text-slate-300">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>satgas.aman@instansi.sch.id</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>WhatsApp Darurat: +62 812-3456-7890</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <a href="satgas/login.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold inline-flex items-center gap-1">
                            Masuk Portal Petugas Satgas &rarr;
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Hak Cipta Dilindungi Undang-Undang.</p>
                <p class="flex items-center gap-1">
                    Dirancang dengan kepedulian untuk menciptakan ruang yang aman dan berkeadilan.
                </p>
            </div>
        </div>
    </footer>

    <!-- Script JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
