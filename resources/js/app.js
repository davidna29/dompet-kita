import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

const rupiah = (angka) =>
    'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(angka) || 0);

window.formatRupiahInput = (value) => {
    const digit = String(value ?? '').replace(/[^\d]/g, '');
    if (digit === '') {
        return '';
    }

    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(digit));
};

/**
 * Parse Indonesian shorthand for the amount inputs. Letters are allowed in the
 * field until blur; formatting only happens after the value is settled.
 */
window.parseRupiahInput = (value) => {
    let teks = String(value ?? '').trim().toLowerCase();
    if (teks === '') {
        return null;
    }

    teks = teks.replace(/rp\.?/g, '').replace(/idr/g, '').trim();

    let pengali = 1;
    if (/(juta|jt)$/.test(teks)) {
        pengali = 1_000_000;
        teks = teks.replace(/(juta|jt)$/, '').trim();
    } else if (/(ribu|rb|k)$/.test(teks)) {
        pengali = 1_000;
        teks = teks.replace(/(ribu|rb|k)$/, '').trim();
    }

    if (!/^[0-9.,\s]+$/.test(teks)) {
        return null;
    }

    if (pengali > 1) {
        const titik = (teks.match(/\./g) || []).length;
        const koma = (teks.match(/,/g) || []).length;

        if (titik > 0 && koma > 0) {
            teks = teks.replace(/\./g, '').replace(',', '.');
        } else if (koma === 1) {
            teks = teks.replace(',', '.');
        } else if (titik === 1) {
            const [depan, belakang] = teks.split('.');
            if (belakang.length > 2) {
                teks = depan + belakang;
            }
        } else if (titik > 1) {
            teks = teks.replace(/\./g, '');
        }
    } else {
        teks = teks.replace(/[^0-9]/g, '');
    }

    if (teks === '' || Number.isNaN(Number(teks))) {
        return null;
    }

    const hasil = Math.round(Number(teks) * pengali);

    return hasil >= 0 ? hasil : null;
};

window.formatRupiahValue = (value) => {
    const hasil = window.parseRupiahInput(value);

    if (hasil === null) {
        return String(value ?? '').trim();
    }

    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(hasil);
};

window.formatRupiah = rupiah;

function cssVar(nama) {
    return getComputedStyle(document.documentElement).getPropertyValue(nama).trim();
}

let grafik = [];

function hancurkanGrafik() {
    grafik.forEach((chart) => chart.destroy());
    grafik = [];
}

function bacaJson(element, kunci, cadangan) {
    const nilai = element.dataset[kunci];
    if (!nilai) {
        return cadangan;
    }

    try {
        return JSON.parse(nilai);
    } catch {
        return cadangan;
    }
}

function gambarGrafik() {
    hancurkanGrafik();

    if (!window.Chart) {
        return;
    }

    const warna = {
        ink: cssVar('--ink'),
        muted: cssVar('--muted'),
        line: cssVar('--line'),
        paper: cssVar('--paper'),
        olive: cssVar('--olive'),
        tomato: cssVar('--tomato'),
        ochre: cssVar('--ochre'),
        sky: cssVar('--sky'),
        sage: cssVar('--sage'),
    };
    const durasiGrafik = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 240;

    Chart.defaults.font.family = cssVar('--font-sans') || 'Karla Variable, sans-serif';
    Chart.defaults.color = warna.muted;
    Chart.defaults.borderColor = warna.line;

    document.querySelectorAll('[data-chart]').forEach((canvas) => {
        const tipe = canvas.dataset.chart;
        const labels = bacaJson(canvas, 'labels', []);
        const values = bacaJson(canvas, 'values', []);
        const colors = bacaJson(canvas, 'colors', [warna.olive]);

        if (labels.length === 0) {
            return;
        }

        const umum = {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: durasiGrafik, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: warna.ink,
                    titleColor: warna.paper,
                    bodyColor: warna.paper,
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (context) => {
                            if (tipe === 'line') {
                                return rupiah(context.parsed.y);
                            }
                            return rupiah(context.parsed.y ?? context.parsed);
                        },
                    },
                },
            },
        };

        if (tipe === 'donut') {
            grafik.push(
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [
                            {
                                data: values,
                                backgroundColor: colors,
                                borderColor: warna.paper,
                                borderWidth: 3,
                                hoverOffset: 3,
                            },
                        ],
                    },
                    options: {
                        ...umum,
                        cutout: '68%',
                        plugins: {
                            ...umum.plugins,
                            legend: { display: false },
                        },
                    },
                })
            );
        }

        if (tipe === 'bars') {
            grafik.push(
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                data: values,
                                backgroundColor: values.map((_, i) =>
                                    i === values.length - 1 ? warna.tomato : warna.olive
                                ),
                                borderRadius: 4,
                                maxBarThickness: 30,
                            },
                        ],
                    },
                    options: {
                        ...umum,
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { font: { size: 10 }, color: warna.muted },
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: warna.line, drawTicks: false },
                                border: { display: false },
                                ticks: {
                                    maxTicksLimit: 4,
                                    font: { size: 9 },
                                    color: warna.muted,
                                    callback: (v) => (v >= 1000000 ? v / 1000000 + 'jt' : v / 1000 + 'rb'),
                                },
                            },
                        },
                    },
                })
            );
        }

        if (tipe === 'line') {
            const gradient = canvas.getContext('2d').createLinearGradient(0, 0, 0, 220);
            gradient.addColorStop(0, warna.olive + '4d');
            gradient.addColorStop(1, warna.olive + '00');

            grafik.push(
                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                data: values,
                                borderColor: warna.olive,
                                backgroundColor: gradient,
                                fill: true,
                                tension: 0.32,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                pointHoverBackgroundColor: warna.tomato,
                            },
                        ],
                    },
                    options: {
                        ...umum,
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { maxTicksLimit: 6, font: { size: 9 }, color: warna.muted },
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: warna.line, drawTicks: false },
                                border: { display: false },
                                ticks: {
                                    maxTicksLimit: 4,
                                    font: { size: 9 },
                                    color: warna.muted,
                                    callback: (v) => (v >= 1000000 ? v / 1000000 + 'jt' : v / 1000 + 'rb'),
                                },
                            },
                        },
                    },
                })
            );
        }
    });
}

function perbaruiWarnaBrowser() {
    const canvas = cssVar('--canvas');

    document.querySelectorAll('[data-theme-color]').forEach((meta) => {
        meta.setAttribute('content', canvas);
    });
}

function terapkanTema(tema, simpan = true, beritahuGrafik = true) {
    const gelap = tema === 'dark';
    document.documentElement.classList.toggle('dark', gelap);

    if (simpan) {
        localStorage.setItem('dompet-tema', gelap ? 'dark' : 'light');
    }

    document.querySelectorAll('[data-theme-label]').forEach((el) => {
        el.textContent = gelap ? 'Mode terang' : 'Mode gelap';
    });

    perbaruiWarnaBrowser();
    if (beritahuGrafik) {
        window.dispatchEvent(
            new CustomEvent('theme-changed', {
                detail: {
                    tema: gelap ? 'dark' : 'light',
                    palet: document.documentElement.dataset.palette || 'olive',
                },
            })
        );
    }
}

function terapkanPalet(palet, simpan = true, beritahuGrafik = true) {
    const pilihan = palet === 'rose' ? 'rose' : 'olive';
    document.documentElement.dataset.palette = pilihan;

    if (simpan) {
        localStorage.setItem('dompet-palet', pilihan);
    }

    document.querySelectorAll('[data-palette-select]').forEach((input) => {
        const aktif = input.value === pilihan;
        input.checked = aktif;

        const label = input.closest('[data-palette-choice]');
        label?.classList.toggle('border-olive', aktif);
        label?.classList.toggle('bg-olive-soft', aktif);
        label?.classList.toggle('text-olive-deep', aktif);
    });

    perbaruiWarnaBrowser();
    if (beritahuGrafik) {
        window.dispatchEvent(
            new CustomEvent('theme-changed', {
                detail: {
                    tema: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                    palet: pilihan,
                },
            })
        );
    }
}

window.toggleTheme = () => {
    terapkanTema(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
};

window.setDompetPalette = (palet) => terapkanPalet(palet);

function terapkanPreferensiTampilan() {
    const paletTersimpan = localStorage.getItem('dompet-palet');
    terapkanPalet(paletTersimpan === 'rose' ? 'rose' : 'olive', false, false);

    const tersimpan = localStorage.getItem('dompet-tema');
    if (tersimpan === 'dark' || (tersimpan === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        terapkanTema('dark', false, false);
    } else {
        terapkanTema('light', false, false);
    }
}

function tutupToast(toast) {
    if (!toast || toast.dataset.menutup === 'true') {
        return;
    }

    toast.dataset.menutup = 'true';
    toast.classList.add('toast--menutup');
    window.setTimeout(() => toast.remove(), 180);
}

document.addEventListener('click', (event) => {
    if (! (event.target instanceof Element)) {
        return;
    }

    const tombol = event.target.closest('[data-toast-close]');
    if (tombol) {
        tutupToast(tombol.closest('[data-toast]'));
    }
});

let promptPasang = null;

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    promptPasang = event;
    document.querySelectorAll('[data-install-app]').forEach((tombol) => {
        tombol.hidden = false;
    });
});

window.addEventListener('theme-changed', () => {
    window.setTimeout(gambarGrafik, 30);
});

function perbaruiBannerOffline() {
    document.querySelectorAll('[data-offline-banner]').forEach((banner) => {
        banner.hidden = navigator.onLine;
    });
}

window.addEventListener('online', perbaruiBannerOffline);
window.addEventListener('offline', perbaruiBannerOffline);

document.addEventListener('livewire:navigating', (event) => {
    event.detail?.onSwap?.(() => terapkanPreferensiTampilan());
});

function inisialisasiHalaman() {
    document.querySelectorAll('[data-theme-toggle]').forEach((tombol) => {
        if (tombol.dataset.themeBound !== 'true') {
            tombol.dataset.themeBound = 'true';
            tombol.addEventListener('click', () => window.toggleTheme());
        }
    });

    document.querySelectorAll('[data-palette-select]').forEach((input) => {
        if (input.dataset.paletteBound !== 'true') {
            input.dataset.paletteBound = 'true';
            input.addEventListener('change', () => {
                if (input.checked) {
                    terapkanPalet(input.value);
                }
            });
        }
    });

    terapkanPreferensiTampilan();

    gambarGrafik();

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        if (toast.dataset.toastInitialized !== 'true') {
            toast.dataset.toastInitialized = 'true';
            window.setTimeout(() => tutupToast(toast), 5200);
        }
    });

    perbaruiBannerOffline();

    document.querySelectorAll('[data-install-app]').forEach((tombol) => {
        tombol.hidden = promptPasang === null;

        if (tombol.dataset.installBound !== 'true') {
            tombol.dataset.installBound = 'true';
            tombol.addEventListener('click', async () => {
                if (!promptPasang) {
                    return;
                }
                promptPasang.prompt();
                await promptPasang.userChoice;
                promptPasang = null;
                tombol.hidden = true;
            });
        }
    });

    if ('serviceWorker' in navigator && !window.dompetServiceWorkerDiminta) {
        window.dompetServiceWorkerDiminta = true;
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
}

document.addEventListener('DOMContentLoaded', inisialisasiHalaman);
document.addEventListener('livewire:navigated', inisialisasiHalaman);

window.addEventListener('beforeunload', hancurkanGrafik);
