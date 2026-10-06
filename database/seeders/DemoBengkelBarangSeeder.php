<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Bengkel;
use App\Models\LokasiPenyimpanan;
use App\Models\Satuan;
use App\Models\SumberDana;
use Illuminate\Database\Seeder;

class DemoBengkelBarangSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data 8 Bengkel Demo
        $bengkelsData = [
            [
                'kode'      => 'BGK-D',
                'nama'      => 'Bengkel Demo',
                'deskripsi' => 'Ruang praktikum presentasi dan showcase multimedia demo',
            ],
            [
                'kode'      => 'BGK-AV',
                'nama'      => 'Bengkel Audio Visual',
                'deskripsi' => 'Konsentrasi keahlian dan lab produksi Audio Visual / Penyiaran',
            ],
            [
                'kode'      => 'BGK-B',
                'nama'      => 'Bengkel Bangunan',
                'deskripsi' => 'Konsentrasi keahlian Teknik Konstruksi dan Properti / Desain Pemodelan Bangunan',
            ],
            [
                'kode'      => 'BGK-GU',
                'nama'      => 'Bengkel Gudang Utama',
                'deskripsi' => 'Pusat logistik material, sarana umum, dan pergudangan sekolah',
            ],
            [
                'kode'      => 'BGK-L',
                'nama'      => 'Bengkel Listrik',
                'deskripsi' => 'Konsentrasi keahlian Teknik Instalasi Tenaga Listrik (TITL)',
            ],
            [
                'kode'      => 'BGK-M',
                'nama'      => 'Bengkel Mesin',
                'deskripsi' => 'Konsentrasi keahlian Teknik Pemesinan dan Fabrikasi Logam',
            ],
            [
                'kode'      => 'BGK-O',
                'nama'      => 'Bengkel Otomotif',
                'deskripsi' => 'Konsentrasi keahlian Teknik Kendaraan Ringan dan Sepeda Motor',
            ],
            [
                'kode'      => 'BGK-TI',
                'nama'      => 'Bengkel Teknologi Informasi',
                'deskripsi' => 'Konsentrasi keahlian Teknik Komputer Jaringan & Rekayasa Perangkat Lunak',
            ],
        ];

        $bengkelMap = [];
        foreach ($bengkelsData as $bData) {
            $bengkelMap[$bData['kode']] = Bengkel::updateOrCreate(
                ['kode' => $bData['kode']],
                $bData
            );
        }

        // 2. Master Sumber Dana (pastikan tersedia)
        $bos  = SumberDana::firstOrCreate(['nama' => 'BOS Reguler'], ['kode' => 'BOS', 'deskripsi' => 'Bantuan Operasional Sekolah Reguler']);
        $bopd = SumberDana::firstOrCreate(['nama' => 'BOPD / Komite'], ['kode' => 'BOPD', 'deskripsi' => 'Bantuan Operasional Pendidikan Daerah & Komite']);
        $dak  = SumberDana::firstOrCreate(['nama' => 'DAK Fisik'], ['kode' => 'DAK', 'deskripsi' => 'Dana Alokasi Khusus Fisik Sarpras']);

        // 3. Master Satuan Umum (pastikan tersedia)
        $satuanList = [
            ['nama' => 'Unit', 'singkatan' => 'unit'],
            ['nama' => 'Pcs',  'singkatan' => 'pcs'],
            ['nama' => 'Set',  'singkatan' => 'set'],
            ['nama' => 'Roll', 'singkatan' => 'roll'],
            ['nama' => 'Box',  'singkatan' => 'box'],
            ['nama' => 'Pack', 'singkatan' => 'pack'],
        ];
        foreach ($satuanList as $s) {
            Satuan::firstOrCreate(['nama' => $s['nama']], $s);
        }

        // 4. Konfigurasi Lokasi dan Barang per Bengkel
        $katalogPerBengkel = [
            // ---------------------------------------------------------
            // 1. BENGKEL DEMO (BGK-D)
            // ---------------------------------------------------------
            'BGK-D' => [
                'lokasi' => [
                    ['kode' => 'L-D-01', 'nama' => 'Lemari Perangkat Demo', 'deskripsi' => 'Penyimpanan proyektor dan presenter'],
                    ['kode' => 'R-D-01', 'nama' => 'Rak Aksesoris Display', 'deskripsi' => 'Penyimpanan kabel display dan toolkit peraga'],
                ],
                'barang' => [
                    [
                        'kode' => 'D-INV-001', 'nama' => 'Proyektor Portable Epson EB-X500', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 6200000, 'stok' => 4, 'min' => 1, 'lokasi' => 'L-D-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Proyektor 3600 ANSI Lumens resolusi XGA untuk presentasi demo alat',
                    ],
                    [
                        'kode' => 'D-INV-002', 'nama' => 'Laser Pointer & Presenter Wireless Logitech R400', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 380000, 'stok' => 8, 'min' => 2, 'lokasi' => 'L-D-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Pointer wireless dengan kontrol slideshow jangkauan hingga 15 meter',
                    ],
                    [
                        'kode' => 'D-INV-003', 'nama' => 'Precision Toolkit 32-in-1 Jakemy', 'jenis' => 'inventaris',
                        'satuan' => 'Set', 'harga' => 185000, 'stok' => 10, 'min' => 2, 'lokasi' => 'R-D-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Set obeng presisi multifungsi untuk bongkar pasang modul demo',
                    ],
                    [
                        'kode' => 'D-BHP-001', 'nama' => 'Baterai Rechargeable AAA Eneloop 4-Pack', 'jenis' => 'bhp',
                        'satuan' => 'Pack', 'harga' => 165000, 'stok' => 20, 'min' => 5, 'lokasi' => 'R-D-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Baterai isi ulang kapasitas 800mAh untuk presenter dan perangkat demo',
                    ],
                    [
                        'kode' => 'D-BHP-002', 'nama' => 'Kain Microfiber Pembersih Optik & Lensa', 'jenis' => 'bhp',
                        'satuan' => 'Pcs', 'harga' => 15000, 'stok' => 40, 'min' => 10, 'lokasi' => 'R-D-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Lap halus pembersih lensa proyektor dan layar monitor demo',
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // 2. BENGKEL AUDIO VISUAL (BGK-AV)
            // ---------------------------------------------------------
            'BGK-AV' => [
                'lokasi' => [
                    ['kode' => 'L-AV-01', 'nama' => 'Dry Cabinet Kamera & Lensa', 'deskripsi' => 'Penyimpanan kamera DSLR, mirrorless, dan mikrofon'],
                    ['kode' => 'R-AV-01', 'nama' => 'Rak Lighting & Kabel Audio', 'deskripsi' => 'Penyimpanan tripod, lampu studio, dan kabel audio'],
                ],
                'barang' => [
                    [
                        'kode' => 'AV-INV-001', 'nama' => 'Kamera DSLR Canon EOS 200D II Kit 18-55mm', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 9800000, 'stok' => 5, 'min' => 1, 'lokasi' => 'L-AV-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Kamera perekaman video 4K dan foto praktikum broadcasting dan multimedia',
                    ],
                    [
                        'kode' => 'AV-INV-002', 'nama' => 'Tripod Video Fluid Head Somita ST-650', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 750000, 'stok' => 8, 'min' => 2, 'lokasi' => 'R-AV-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Tripod video profesional pergerakan pan & tilt halus dengan spreader',
                    ],
                    [
                        'kode' => 'AV-INV-003', 'nama' => 'Wireless Clip-On Microphone Boya BY-XM6', 'jenis' => 'inventaris',
                        'satuan' => 'Set', 'harga' => 1650000, 'stok' => 6, 'min' => 2, 'lokasi' => 'L-AV-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Mic nirkabel dual-channel 2.4GHz untuk wawancara dan liputan video',
                    ],
                    [
                        'kode' => 'AV-BHP-001', 'nama' => 'Kabel Audio Canare L-2T2S Roll 50m', 'jenis' => 'bhp',
                        'satuan' => 'Roll', 'harga' => 850000, 'stok' => 3, 'min' => 1, 'lokasi' => 'R-AV-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Kabel mikrofon profesional unbalance/balance low-noise shielded',
                    ],
                    [
                        'kode' => 'AV-BHP-002', 'nama' => 'Jack Audio XLR Cannon Male & Female Set', 'jenis' => 'bhp',
                        'satuan' => 'Set', 'harga' => 35000, 'stok' => 50, 'min' => 10, 'lokasi' => 'R-AV-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Konektor XLR 3-pin metal housing untuk praktikum perakitan kabel audio',
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // 3. BENGKEL BANGUNAN (BGK-B)
            // ---------------------------------------------------------
            'BGK-B' => [
                'lokasi' => [
                    ['kode' => 'L-B-01', 'nama' => 'Lemari Alat Ukur Survei', 'deskripsi' => 'Penyimpanan theodolite, waterpass, dan laser measure'],
                    ['kode' => 'R-B-01', 'nama' => 'Rak Perkakas Konstruksi Kayu/Batu', 'deskripsi' => 'Penyimpanan gergaji, sendok semen, dan bor'],
                ],
                'barang' => [
                    [
                        'kode' => 'B-INV-001', 'nama' => 'Theodolite Digital Ruide ET-02', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 14500000, 'stok' => 3, 'min' => 1, 'lokasi' => 'L-B-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Alat ukur sudut dan pemetaan elevasi digital untuk survei konstruksi',
                    ],
                    [
                        'kode' => 'B-INV-002', 'nama' => 'Mesin Bor Hammer Drill Bosch GBH 2-26 DRE', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 2100000, 'stok' => 6, 'min' => 2, 'lokasi' => 'R-B-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Bor rotary hammer 800W untuk pekerjaan pengeboran beton dan dinding',
                    ],
                    [
                        'kode' => 'B-INV-003', 'nama' => 'Waterpass Magnetik 60cm Stanley FatMax', 'jenis' => 'inventaris',
                        'satuan' => 'Pcs', 'harga' => 275000, 'stok' => 12, 'min' => 3, 'lokasi' => 'L-B-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Alat pengukur kerataan horizontal dan vertikal berpresisi tinggi',
                    ],
                    [
                        'kode' => 'B-BHP-001', 'nama' => 'Mata Bor Beton Set SDS Plus 5 Pcs', 'jenis' => 'bhp',
                        'satuan' => 'Set', 'harga' => 140000, 'stok' => 25, 'min' => 5, 'lokasi' => 'R-B-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Set mata bor hammer diameter 6, 8, 10, 12mm material tungsten carbide',
                    ],
                    [
                        'kode' => 'B-BHP-002', 'nama' => 'Paku Kayu Konstruksi 5cm & 7cm Campur', 'jenis' => 'bhp',
                        'satuan' => 'Box', 'harga' => 85000, 'stok' => 30, 'min' => 5, 'lokasi' => 'R-B-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Paku besi galvanis isi 5kg per box untuk bekisting dan kusen',
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // 4. BENGKEL GUDANG UTAMA (BGK-GU)
            // ---------------------------------------------------------
            'BGK-GU' => [
                'lokasi' => [
                    ['kode' => 'L-GU-01', 'nama' => 'Area Alat Berat Logistik', 'deskripsi' => 'Penempatan hand pallet, tangga, dan tangga lipat'],
                    ['kode' => 'R-GU-01', 'nama' => 'Rak Material Packing & Safety', 'deskripsi' => 'Penyimpanan lakban, wrapping, dan helm proyek'],
                ],
                'barang' => [
                    [
                        'kode' => 'GU-INV-001', 'nama' => 'Hand Pallet Truck 2.5 Ton Dalton', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 4750000, 'stok' => 2, 'min' => 1, 'lokasi' => 'L-GU-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Truk garpu angkat hidrolik beban maksimal 2500kg untuk bongkar muat barang',
                    ],
                    [
                        'kode' => 'GU-INV-002', 'nama' => 'Tangga Teleskopik Aluminium 3.8 Meter', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 1250000, 'stok' => 4, 'min' => 1, 'lokasi' => 'L-GU-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Tangga lipat teleskopik fleksibel bahan alloy ringan dan kokoh',
                    ],
                    [
                        'kode' => 'GU-INV-003', 'nama' => 'Troli Barang Lipat Heavy Duty 300kg Prestar', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 1650000, 'stok' => 5, 'min' => 1, 'lokasi' => 'L-GU-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Troli dorong platform besi bantalan roda karet senyap anti-selip',
                    ],
                    [
                        'kode' => 'GU-BHP-001', 'nama' => 'Stretch Film Plastik Wrapping 50cm x 300m', 'jenis' => 'bhp',
                        'satuan' => 'Roll', 'harga' => 75000, 'stok' => 35, 'min' => 10, 'lokasi' => 'R-GU-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Plastik pembungkus barang palet elastis melindungi dari debu dan air',
                    ],
                    [
                        'kode' => 'GU-BHP-002', 'nama' => 'Lakban Bening Daimaru 2 Inch x 100 Yard', 'jenis' => 'bhp',
                        'satuan' => 'Box', 'harga' => 110000, 'stok' => 25, 'min' => 5, 'lokasi' => 'R-GU-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Lakban isolasi kardus tebal isi 6 roll per pack',
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // 5. BENGKEL LISTRIK (BGK-L)
            // ---------------------------------------------------------
            'BGK-L' => [
                'lokasi' => [
                    ['kode' => 'L-L-01', 'nama' => 'Lemari Instrumen Pengukuran', 'deskripsi' => 'Penyimpanan multimeter, megger, dan clamp meter'],
                    ['kode' => 'R-L-01', 'nama' => 'Rak Perkakas Kabel & Instalasi', 'deskripsi' => 'Penyimpanan tang kupas, solder, dan kabel instalasi'],
                ],
                'barang' => [
                    [
                        'kode' => 'L-INV-001', 'nama' => 'Digital Multimeter Sanwa CD800a', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 680000, 'stok' => 15, 'min' => 3, 'lokasi' => 'L-L-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Multimeter digital akurasi 0.7% dengan pelindung bodi elastis',
                    ],
                    [
                        'kode' => 'L-INV-002', 'nama' => 'Tang Ampere / Digital Clamp Meter Kyoritsu 2002PA', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 1450000, 'stok' => 6, 'min' => 2, 'lokasi' => 'L-L-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Tang ampere AC hingga 2000A untuk pengukuran arus beban panel daya',
                    ],
                    [
                        'kode' => 'L-INV-003', 'nama' => 'Solder Station Digital Quick 936A', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 520000, 'stok' => 12, 'min' => 3, 'lokasi' => 'R-L-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Peralatan solder temperatur stabil dengan knob kontrol analog presisi',
                    ],
                    [
                        'kode' => 'L-BHP-001', 'nama' => 'Timah Solder 60/40 Asahi 0.8mm 500g', 'jenis' => 'bhp',
                        'satuan' => 'Roll', 'harga' => 320000, 'stok' => 15, 'min' => 3, 'lokasi' => 'R-L-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Kawat timah solder berkualitas tinggi dengan inti flux rosin',
                    ],
                    [
                        'kode' => 'L-BHP-002', 'nama' => 'Isolasi Listrik PVC Nitto No.21 Hitam', 'jenis' => 'bhp',
                        'satuan' => 'Pcs', 'harga' => 12000, 'stok' => 80, 'min' => 20, 'lokasi' => 'R-L-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Pita isolasi listrik tahan tegangan hingga 600V berdaya rekat tinggi',
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // 6. BENGKEL MESIN (BGK-M)
            // ---------------------------------------------------------
            'BGK-M' => [
                'lokasi' => [
                    ['kode' => 'L-M-01', 'nama' => 'Lemari Alat Ukur Presisi Mesin', 'deskripsi' => 'Penyimpanan jangka sorong, mikrometer, dan dial gauge'],
                    ['kode' => 'R-M-01', 'nama' => 'Rak Perkakas Potong & Gerinda', 'deskripsi' => 'Penyimpanan mesin gerinda, pahat bubut, dan kacamata safety'],
                ],
                'barang' => [
                    [
                        'kode' => 'M-INV-001', 'nama' => 'Jangka Sorong Vernier Caliper 150mm Mitutoyo', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 650000, 'stok' => 18, 'min' => 4, 'lokasi' => 'L-M-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Sigmat mekanik presisi 0.05mm berbahan stainless steel keras',
                    ],
                    [
                        'kode' => 'M-INV-002', 'nama' => 'Mikrometer Luar Outside Micrometer 0-25mm Mitutoyo', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 890000, 'stok' => 10, 'min' => 2, 'lokasi' => 'L-M-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Alat ukur ketebalan presisi 0.01mm dengan ratchet stop pelindung torsi',
                    ],
                    [
                        'kode' => 'M-INV-003', 'nama' => 'Mesin Gerinda Tangan 4 Inch Makita 9553B', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 780000, 'stok' => 8, 'min' => 2, 'lokasi' => 'R-M-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Mesin gerinda motor bertenaga 710 watt untuk memotong dan menghaluskan logam',
                    ],
                    [
                        'kode' => 'M-BHP-001', 'nama' => 'Mata Gerinda Potong 4 Inch WD (Isi 20 Pcs)', 'jenis' => 'bhp',
                        'satuan' => 'Box', 'harga' => 95000, 'stok' => 30, 'min' => 5, 'lokasi' => 'R-M-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Batu gerinda potong tipis 105 x 1.2 x 16mm untuk plat dan pipa besi',
                    ],
                    [
                        'kode' => 'M-BHP-002', 'nama' => 'Sarung Tangan Las Kulit Sapi Safety 14 Inch', 'jenis' => 'bhp',
                        'satuan' => 'Pcs', 'harga' => 45000, 'stok' => 40, 'min' => 10, 'lokasi' => 'R-M-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Sarung tangan pelindung percikan api dan panas saat praktikum las',
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // 7. BENGKEL OTOMOTIF (BGK-O)
            // ---------------------------------------------------------
            'BGK-O' => [
                'lokasi' => [
                    ['kode' => 'L-O-01', 'nama' => 'Lemari Special Service Tools (SST)', 'deskripsi' => 'Penyimpanan scanner injeksi, kunci torsi, dan treker'],
                    ['kode' => 'R-O-01', 'nama' => 'Rak Perkakas Tangan Mekanik', 'deskripsi' => 'Penyimpanan kunci ring-pas, obeng ketok, dan tang snap ring'],
                ],
                'barang' => [
                    [
                        'kode' => 'O-INV-001', 'nama' => 'Kunci Ring Pas Set 14 Pcs 8-24mm Tekiro', 'jenis' => 'inventaris',
                        'satuan' => 'Set', 'harga' => 480000, 'stok' => 15, 'min' => 3, 'lokasi' => 'R-O-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Kunci kombinasi chrome vanadium anti-karat dengan pouch kanvas gantung',
                    ],
                    [
                        'kode' => 'O-INV-002', 'nama' => 'Impact Wrench Cordless 20V DeWalt DCF899', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 3950000, 'stok' => 4, 'min' => 1, 'lokasi' => 'L-O-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Alat pembuka baut roda torsi tinggi tanpa kabel dengan baterai lithium',
                    ],
                    [
                        'kode' => 'O-INV-003', 'nama' => 'Tyre Pressure Gauge Digital Tekiro', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 195000, 'stok' => 8, 'min' => 2, 'lokasi' => 'L-O-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Pengukur tekanan angin ban digital dengan layar LCD backlight',
                    ],
                    [
                        'kode' => 'O-BHP-001', 'nama' => 'Oli Mesin 4T 10W-40 Pertamina Enduro 1L', 'jenis' => 'bhp',
                        'satuan' => 'Pcs', 'harga' => 58000, 'stok' => 35, 'min' => 10, 'lokasi' => 'R-O-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Pelumas mesin sintetis untuk praktikum servis berkala kendaraan roda dua',
                    ],
                    [
                        'kode' => 'O-BHP-002', 'nama' => 'Kain Majun / Lap Katun Halus Jahit 1kg', 'jenis' => 'bhp',
                        'satuan' => 'Pack', 'harga' => 20000, 'stok' => 50, 'min' => 15, 'lokasi' => 'R-O-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Lap pembersih gemuk dan oli mesin praktikum otomotif',
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // 8. BENGKEL TEKNOLOGI INFORMASI (BGK-TI)
            // ---------------------------------------------------------
            'BGK-TI' => [
                'lokasi' => [
                    ['kode' => 'L-TI-01', 'nama' => 'Rack Server & Jaringan', 'deskripsi' => 'Penyimpanan router enterprise, switch manageable, dan server lab'],
                    ['kode' => 'R-TI-01', 'nama' => 'Rak Toolset Jaringan Komputer', 'deskripsi' => 'Penyimpanan tang crimping, LAN tester, dan kabel roll'],
                ],
                'barang' => [
                    [
                        'kode' => 'TI-INV-001', 'nama' => 'MikroTik RB450Gx4 Gigabit Routerboard', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 1650000, 'stok' => 10, 'min' => 2, 'lokasi' => 'L-TI-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Router 5-port Gigabit Ethernet quad-core untuk praktikum routing dinamis & firewall',
                    ],
                    [
                        'kode' => 'TI-INV-002', 'nama' => 'Network Cable Tester Digital LCD Noyafa NF-8209', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 550000, 'stok' => 8, 'min' => 2, 'lokasi' => 'R-TI-01', 'sumber' => $bos->id,
                        'deskripsi' => 'Tester kabel LAN multi-fungsi dengan pelacak kabel, tes PoE, dan NCV',
                    ],
                    [
                        'kode' => 'TI-INV-003', 'nama' => 'Switch Cisco Catalyst 24-Port WS-C2960-24TT-L', 'jenis' => 'inventaris',
                        'satuan' => 'Unit', 'harga' => 2800000, 'stok' => 5, 'min' => 1, 'lokasi' => 'L-TI-01', 'sumber' => $dak->id,
                        'deskripsi' => 'Switch manageable Layer 2 untuk praktikum konfigurasi VLAN dan Trunking',
                    ],
                    [
                        'kode' => 'TI-BHP-001', 'nama' => 'Konektor RJ45 Cat6 Commscope / AMP (Box 100 Pcs)', 'jenis' => 'bhp',
                        'satuan' => 'Box', 'harga' => 220000, 'stok' => 25, 'min' => 5, 'lokasi' => 'R-TI-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Modular plug RJ45 Cat6 original dengan pin gold plated 50 micron',
                    ],
                    [
                        'kode' => 'TI-BHP-002', 'nama' => 'Patch Cord UTP Cat6 2 Meter Belden Pabrikan', 'jenis' => 'bhp',
                        'satuan' => 'Pcs', 'harga' => 28000, 'stok' => 45, 'min' => 10, 'lokasi' => 'R-TI-01', 'sumber' => $bopd->id,
                        'deskripsi' => 'Kabel LAN patch cord siap pakai untuk koneksi antar perangkat lab komputer',
                    ],
                ],
            ],
        ];

        // 5. Eksekusi Seeding Lokasi & Barang
        foreach ($katalogPerBengkel as $bengkelKode => $data) {
            $bengkel = $bengkelMap[$bengkelKode] ?? null;
            if (!$bengkel) {
                continue;
            }

            // Simpan lokasi
            $lokasiMap = [];
            foreach ($data['lokasi'] as $loc) {
                $lokasiMap[$loc['kode']] = LokasiPenyimpanan::updateOrCreate(
                    [
                        'bengkel_id' => $bengkel->id,
                        'kode'       => $loc['kode'],
                    ],
                    [
                        'bengkel_id' => $bengkel->id,
                        'kode'       => $loc['kode'],
                        'nama'       => $loc['nama'],
                        'deskripsi'  => $loc['deskripsi'],
                    ]
                );
            }

            // Simpan barang
            foreach ($data['barang'] as $item) {
                $lokasiId = isset($lokasiMap[$item['lokasi']]) ? $lokasiMap[$item['lokasi']]->id : null;

                Barang::updateOrCreate(
                    [
                        'bengkel_id'  => $bengkel->id,
                        'kode_barang' => $item['kode'],
                    ],
                    [
                        'bengkel_id'            => $bengkel->id,
                        'lokasi_penyimpanan_id' => $lokasiId,
                        'sumber_dana_id'        => $item['sumber'],
                        'kode_barang'           => $item['kode'],
                        'nama'                  => $item['nama'],
                        'jenis_barang'          => $item['jenis'],
                        'satuan'                => $item['satuan'],
                        'harga'                 => $item['harga'],
                        'stok_total'            => $item['stok'],
                        'stok_tersedia'         => $item['stok'],
                        'stok_dipinjam'         => 0,
                        'stok_rusak'            => 0,
                        'minimum_stok'          => $item['min'],
                        'deskripsi'             => $item['deskripsi'],
                    ]
                );
            }
        }
    }
}

