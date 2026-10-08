# app.py — Flask API untuk prediksi sentimen (Model SVM)
from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import re
import os

try:
    from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
    from Sastrawi.StopWordRemover.StopWordRemoverFactory import StopWordRemoverFactory
except ImportError:
    print("Warning: Library Sastrawi belum diinstall. Jalankan 'pip install Sastrawi'")

app = Flask(__name__)
CORS(app)

svm = None
tfidf = None
stemmer = None
stopword = None

def load_models():
    global svm, tfidf, stemmer, stopword
    try:
        if os.path.exists('svm_sentiment.pkl') and os.path.exists('tfidf_vectorizer.pkl'):
            svm = joblib.load('svm_sentiment.pkl')
            tfidf = joblib.load('tfidf_vectorizer.pkl')
            print("Model SVM dan TF-IDF berhasil dimuat!")
        else:
            print("WARNING: File model (svm_sentiment.pkl / tfidf_vectorizer.pkl) tidak ditemukan di folder ini.")
            
        try:
            stemmer = StemmerFactory().create_stemmer()
            stopword = StopWordRemoverFactory().create_stop_word_remover()
        except NameError:
            pass
    except Exception as e:
        print(f"Error loading models: {e}")

def preprocess(t):
    global stemmer, stopword
    t = t.lower()
    t = re.sub(r'https?://\S+|www\.\S+', '', t)
    t = re.sub(r'@\w+|#\w+', '', t)
    t = re.sub(r'\d+', '', t)
    t = re.sub(r'[^\w\s]', '', t)
    t = re.sub(r'\s+', ' ', t).strip()
    
    if stopword and stemmer:
        t = stopword.remove(t)
        t = stemmer.stem(t)
    return t
 
@app.route('/predict', methods=['POST'])
def predict():
    global svm, tfidf
    
    if svm is None or tfidf is None:
        return jsonify({'error': 'Model belum di-load'}), 500

    try:
        data = request.get_json(force=True)
        teks = data.get('teks', '')
        
        if not teks:
            return jsonify({'error': 'Teks kosong'}), 400
            
        clean = preprocess(teks)
        vec   = tfidf.transform([clean])
        label = svm.predict(vec)[0]
        
        return jsonify({
            'teks_asli': teks,
            'teks_clean': clean, 
            'sentimen': label
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    load_models()
    app.run(host='127.0.0.1', port=5000, debug=True)
