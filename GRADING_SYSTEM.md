# Sistem Penilaian Per Kategori

## Deskripsi
Sistem ini menghitung nilai ujian secara keseluruhan dan juga **per kategori**. Setiap kategori memiliki kumpulan soal dengan bobot nilai (grade) masing-masing.

## Contoh Penggunaan

### 1. Menghitung Nilai Keseluruhan
```php
$userAnswer = UserAnswer::find(1);
$grade = $userAnswer->calculateGrade();

// Output:
// [
//     'total' => 90,          // Total bobot semua soal
//     'earned' => 75,         // Nilai yang didapat
//     'percentage' => 83.33   // Persentase
// ]
```

### 2. Menghitung Nilai Per Kategori
```php
$userAnswer = UserAnswer::find(1);
$gradeByCategory = $userAnswer->calculateGradeByCategory();

// Output:
// [
//     [
//         'category_id' => 1,
//         'category_name' => 'Programming',
//         'total_grade' => 25,      // Total bobot soal di kategori ini
//         'earned_grade' => 20,     // Nilai yang didapat di kategori ini
//         'percentage' => 80        // Persentase kategori ini
//     ],
//     [
//         'category_id' => 2,
//         'category_name' => 'Robotics',
//         'total_grade' => 30,
//         'earned_grade' => 25,
//         'percentage' => 83.33
//     ],
//     [
//         'category_id' => 3,
//         'category_name' => 'Logic & Mathematics',
//         'total_grade' => 35,
//         'earned_grade' => 30,
//         'percentage' => 85.71
//     ]
// ]
```

### 3. Mendapatkan Hasil Detail (Keseluruhan + Per Kategori)
```php
$userAnswer = UserAnswer::find(1);
$detailedResult = $userAnswer->getDetailedResult();

// Output:
// [
//     'overall' => [
//         'total' => 90,
//         'earned' => 75,
//         'percentage' => 83.33
//     ],
//     'by_category' => [
//         [
//             'category_id' => 1,
//             'category_name' => 'Programming',
//             'total_grade' => 25,
//             'earned_grade' => 20,
//             'percentage' => 80
//         ],
//         // ... kategori lainnya
//     ]
// ]
```

### 4. Menghitung Nilai User untuk Kategori Tertentu
```php
$category = Category::find(1);
$userAnswer = UserAnswer::find(1);
$categoryGrade = $category->calculateUserGrade($userAnswer);

// Output:
// [
//     'category_name' => 'Programming',
//     'total_grade' => 25,
//     'earned_grade' => 20,
//     'percentage' => 80,
//     'correct_answers' => 1.6,    // Jumlah jawaban benar (proporsional)
//     'total_questions' => 2       // Total soal di kategori
// ]
```

## Struktur Database

### Table: categories
- `id` - ID kategori
- `period_id` - Foreign key ke periods
- `name` - Nama kategori (contoh: "Programming", "Robotics")
- `order` - Urutan kategori
- `descriptions` - Deskripsi kategori

### Table: questions
- `id` - ID soal
- `category_id` - Foreign key ke categories
- `question` - Teks soal
- `type` - Tipe soal ('input' atau 'options')
- `grade` - **Bobot nilai soal** (penting untuk perhitungan per kategori)
- `order` - Urutan soal

### Table: user_answer_items
- `id` - ID jawaban item
- `user_answer_id` - Foreign key ke user_answers
- `question_id` - Foreign key ke questions
- `question_option_id` - Foreign key ke question_options (nullable)
- `answer` - Jawaban teks untuk soal tipe input (nullable)

## Cara Kerja Penilaian Per Kategori

1. **Ambil semua kategori** dari periode yang sedang dikerjakan
2. **Untuk setiap kategori**:
   - Hitung total bobot (grade) dari semua soal di kategori tersebut
   - Cari jawaban user untuk setiap soal di kategori
   - Cocokkan dengan answer_key
   - Jika benar, tambahkan bobot soal ke nilai yang didapat
3. **Hitung persentase** per kategori: (nilai_didapat / total_bobot) × 100

## Contoh Kasus

### Periode: Seleksi Robotik 2025

#### Kategori 1: Programming (Total Bobot: 25)
- Soal 1: "Apa kepanjangan HTML?" (Bobot: 10) → Benar → +10
- Soal 2: "Bahasa web populer?" (Bobot: 15) → Salah → +0
- **Nilai Kategori: 10/25 = 40%**

#### Kategori 2: Robotics (Total Bobot: 30)
- Soal 3: "Apa kepanjangan PWM?" (Bobot: 10) → Benar → +10
- Soal 4: "Sensor jarak?" (Bobot: 20) → Benar → +20
- **Nilai Kategori: 30/30 = 100%**

#### Kategori 3: Logic & Math (Total Bobot: 35)
- Soal 5: "15 + 25 × 2?" (Bobot: 15) → Benar → +15
- Soal 6: "Nilai ROBOT?" (Bobot: 20) → Salah → +0
- **Nilai Kategori: 15/35 = 42.86%**

### Nilai Keseluruhan
- **Total Bobot**: 90 (25 + 30 + 35)
- **Nilai Didapat**: 55 (10 + 30 + 15)
- **Persentase**: 61.11%

## Fitur Admin

Admin dapat:
1. Melihat nilai keseluruhan participant
2. Melihat breakdown nilai per kategori
3. Mengatur apakah hasil dapat di-review (`show_result` di table periods)
4. Mengatur apakah nilai diperlihatkan (`show_grade` di table periods)

## Fitur Participant

Participant dapat:
1. Melihat history pengerjaan (jika `show_result` = true)
2. Melihat nilai (jika `show_grade` = true)
3. Melihat nilai per kategori untuk evaluasi
