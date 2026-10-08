# Analisis-Sentimen-Ulasan-Game-Petualangan-Jokowi-
Sentiment analysis of 2,162 Google Play reviews of a mobile game: scraping, lexicon labeling, TF-IDF, Naive Bayes vs SVM (84.9% accuracy) vs RoBERTa, WordCloud, plus a rule-based chatbot deployed with XAMPP and Flask.

# Analisis Sentimen Ulasan Game "Petualangan Jokowi" (Naïve Bayes, SVM, RoBERTa) + Chatbot

Proyek NLP end-to-end untuk menganalisis sentimen ulasan Google Play Store game **Petualangan Jokowi** (`com.rio.ahok`): dari scraping, pelabelan, preprocessing, ekstraksi fitur, klasifikasi, hingga chatbot yang di-deploy lewat XAMPP. Dikerjakan sebagai bagian pelatihan Data Analyst / Data Science di PPKD Jakarta Selatan.

> **Catatan:** yang dianalisis adalah sentimen terhadap **game** (gameplay, bug, karakter), bukan pandangan politik terhadap tokoh mana pun.



## 🌟 Fitur Utama

- **Scraping Data**: mengambil 2.162 ulasan Play Store (1 Maret 2020 - 1 Agustus 2026) dengan `google-play-scraper`. Kolom yang disimpan: `id`, `teks`, `tanggal`, `sumber` (tanpa nama pengguna).
- **Pelabelan Semi-Otomatis**: auto-label berbasis leksikon (31 kata positif, 38 kata negatif) lalu koreksi manual pada 50 baris pertama.
  Distribusi final: netral 1.094, positif 862, negatif 206.
- **Preprocessing Teks**: case folding, penghapusan URL, mention, hashtag, angka, tanda baca, dan emoji, lalu stop word removal dan stemming dengan Sastrawi.
- **Ekstraksi Fitur**: Bag of Words dan TF-IDF (500 fitur).
- **Klasifikasi**: Naïve Bayes dan SVM (LinearSVC), pembagian data 80:20 stratified (`random_state=42`) dilakukan **sebelum** vektorisasi untuk menghindari data leakage.
- **Model Pre-trained**: evaluasi `w11wo/indonesian-roberta-base-sentiment-classifier` (Hugging Face) sebagai pembanding.
- **Visualisasi**: WordCloud sentimen positif dan negatif serta tabel top 10 kata per kelas.
- **Chatbot Layanan Pelanggan**: front-end HTML/CSS/JS, back-end PHP rule-based dengan 6 intent, dan opsional Flask API yang memakai model SVM untuk menebak sentimen pesan pengguna.

## 📊 Hasil

| Model | Akurasi | Macro F1 | F1 kelas negatif |
|---|---|---|---|
| Naïve Bayes (TF-IDF) | 78,30% | 0,64 | 0,29 |
| SVM / LinearSVC (TF-IDF) | **84,91%** | **0,79** | **0,63** |
| RoBERTa Indonesia (pre-trained) | 51,73% | 0,48 | 0,31 |

Kata dominan: positif (*game, bagus, seru, keren, mantap*), negatif (*susah, burik, karakter, jelek, bug*).

**Cara membaca hasil ini:**
- Evaluasi RoBERTa memakai 433 baris dari data mentah, sedangkan Naïve Bayes dan SVM memakai 424 baris dari data yang sudah dibersihkan. Jadi perbandingannya **tidak sepenuhnya setara**.
- Label acuan berasal dari leksikon dengan koreksi manual terbatas. Ulasan campuran seperti "banyak bug tapi gameplay seru" berlabel netral, sehingga skor model yang membaca konteks bisa tampak rendah.
- Kelas negatif hanya sekitar 10% data, sehingga recall kelas negatif lebih rendah, terutama pada Naïve Bayes (0,17).

## 🛠️ Teknologi & Perpustakaan

- **Bahasa & lingkungan**: Python 3.10+ (dijalankan di Google Colab)
- **Scraping**: google-play-scraper
- **NLP & ML**: pandas, scikit-learn, Sastrawi, emoji, joblib, transformers, torch
- **Visualisasi**: matplotlib, wordcloud
- **Chatbot**: HTML5, CSS3, JavaScript (fetch/AJAX), PHP, XAMPP (Apache), Flask + Flask-CORS (opsional)

## 📋 Struktur Direktori

```
sentiment-analysis-petualangan-jokowi/
├── notebooks/
│   ├── Scraping.ipynb
│   ├── Labeling.ipynb
│   ├── Preprocessing.ipynb
│   ├── FeatureExtraction.ipynb
│   ├── Klasifikasi.ipynb
│   ├── DistilBERT.ipynb
│   └── WordCloud.ipynb
├── chatbot/
│   ├── index.html
│   ├── style.css
│   ├── script.js
│   ├── chat.php
│   └── app.py                    # Flask API (opsional)
├── models/
│   ├── svm_sentiment.pkl
│   └── tfidf_vectorizer.pkl
├── data/
│   └── sample_reviews.csv        # sampel, bukan data lengkap
├── images/                       # WordCloud dan screenshot chatbot
├── requirements.txt
└── README.md
```

## 🚀 Panduan Menjalankan

### 1. Notebook (T1 - T7)
1. Buka notebook di Google Colab (atau Jupyter) dan jalankan berurutan **T1 sampai T7**. Keluaran satu notebook menjadi masukan notebook berikutnya (misalnya `data_preprocessed.csv` dipakai T4, T5, dan T7).
2. Path file di notebook masih mengarah ke Google Drive/Colab pribadi (misalnya `/content/drive/MyDrive/...`). **Sesuaikan path** dengan lokasi file di lingkunganmu.
3. Dependensi: `pip install -r requirements.txt`

### 2. Chatbot via XAMPP
1. Instal XAMPP, lalu jalankan **Apache** dari XAMPP Control Panel.
2. Salin folder `chatbot/` ke `C:\xampp\htdocs\`.
3. Buka `http://localhost/chatbot` di browser.

### 3. (Opsional) Flask API untuk prediksi sentimen
1. Taruh `svm_sentiment.pkl` dan `tfidf_vectorizer.pkl` di folder yang sama dengan `app.py`.
2. `pip install flask flask-cors Sastrawi joblib scikit-learn`
3. Jalankan `python app.py` (berjalan di `http://127.0.0.1:5000`).
4. Di chatbot, ketik misalnya *"analisis sentimen: gamenya burik banyak bug"*.

> API ini hanya untuk penggunaan lokal. Jangan dibuka ke publik tanpa autentikasi.

## 📖 Alur Proyek

1. **Scraping**: ambil ulasan Play Store.
2. **Labeling**: auto-label leksikon, koreksi manual 50 baris.
3. **Preprocessing**: cleaning, stop word, stemming.
4. **Feature Extraction**: bandingkan BoW dan TF-IDF.
5. **Klasifikasi**: latih dan evaluasi Naïve Bayes dan SVM, simpan model `.pkl`.
6. **Model Pre-trained**: uji RoBERTa Indonesia dan bandingkan.
7. **WordCloud**: visualisasi kata dominan per kelas.
8. **Chatbot**: bangun dan deploy chatbot (6 intent: salam, terima_kasih, tentang_game, karakter, gameplay, bug_dan_masalah).

![Tampilan Chatbot](https://github.com/abadillahalqalam2-ship-it/Analisis-Sentimen-Ulasan-Game-Petualangan-Jokowi-/blob/main/chatbotss.PNG)

## ⚠️ Keterbatasan

- Label berasal dari leksikon dengan koreksi manual hanya 50 baris, sehingga mengandung noise (misalnya sarkasme dan ulasan campuran).
- Distribusi kelas tidak seimbang (negatif sekitar 10%).
- Perbandingan RoBERTa dengan Naïve Bayes dan SVM belum setara (jumlah baris uji berbeda).
- Chatbot bersifat rule-based (pencocokan kata kunci) dan hanya mencakup 6 topik.
- Data lengkap tidak disertakan di repo. Gunakan notebook scraping untuk mengambil ulang ulasan, dengan menghormati Terms of Service Google Play dan jeda antar permintaan.
