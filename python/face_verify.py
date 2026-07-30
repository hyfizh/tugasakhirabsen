import sys
import os
import json
import cv2
import numpy as np

# Try importing dlib
DLIB_AVAILABLE = False
dlib_detector = None
try:
    import dlib
    DLIB_AVAILABLE = True
    dlib_detector = dlib.get_frontal_face_detector()
except Exception:
    DLIB_AVAILABLE = False

# Try importing face_recognition
FACE_REC_AVAILABLE = False
try:
    import face_recognition
    FACE_REC_AVAILABLE = True
except Exception:
    FACE_REC_AVAILABLE = False

def extract_face_roi(img, is_dataset=False):
    """
    Extract face ROI using Dlib HOG & OpenCV fallbacks.
    Returns normalized 128x128 grayscale face image or None if NO face is in frame.
    """
    if img is None:
        return None

    h, w = img.shape[:2]
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY) if len(img.shape) == 3 else img.copy()

    faces = []

    # 1. Dlib HOG Detector
    if DLIB_AVAILABLE and dlib_detector is not None:
        try:
            dlib_rects = dlib_detector(gray, 1)
            for rect in dlib_rects:
                x1, y1 = max(0, rect.left()), max(0, rect.top())
                bw, bh = min(w - x1, rect.width()), min(h - y1, rect.height())
                if bw > 25 and bh > 25:
                    faces.append((x1, y1, bw, bh))
        except Exception:
            pass

    # 2. OpenCV Haar Cascade
    if len(faces) == 0:
        cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
        if os.path.exists(cascade_path):
            face_cascade = cv2.CascadeClassifier(cascade_path)
            for scale in [1.05, 1.1, 1.2]:
                detected = face_cascade.detectMultiScale(gray, scaleFactor=scale, minNeighbors=4, minSize=(40, 40))
                if len(detected) > 0:
                    faces = list(detected)
                    break

    # STRICT NO-FACE CHECK: If no face is detected, return None (DO NOT FALLBACK TO ROOM BACKGROUND)
    if len(faces) == 0:
        if is_dataset:
            # If dataset photo, allow center crop fallback
            x, y, bw, bh = int(w * 0.15), int(h * 0.10), int(w * 0.70), int(h * 0.80)
        else:
            return None
    else:
        x, y, bw, bh = max(faces, key=lambda r: r[2] * r[3])

    x = max(0, min(x, w - 1))
    y = max(0, min(y, h - 1))
    bw = max(10, min(bw, w - x))
    bh = max(10, min(bh, h - y))

    face_crop = gray[y:y+bh, x:x+bw]
    face_resized = cv2.resize(face_crop, (128, 128))
    face_norm = cv2.equalizeHist(face_resized)
    return face_norm

def verify_face_strict(path_dataset, path_live):
    """
    Strict Face Detection & Verification Engine.
    If NO face is in camera frame, returns similarity: 0.0 & face_detected: false.
    """
    if not os.path.exists(path_dataset) or not os.path.exists(path_live):
        return {
            "status": "error",
            "matched": False,
            "face_detected": False,
            "similarity": 0.0,
            "message": "File gambar dataset atau live kamera tidak ditemukan."
        }

    img_ds = cv2.imread(path_dataset)
    img_live = cv2.imread(path_live)

    if img_ds is None or img_live is None:
        return {
            "status": "error",
            "matched": False,
            "face_detected": False,
            "similarity": 0.0,
            "message": "Gagal membaca berkas gambar."
        }

    # Extract Face ROIs
    roi_ds = extract_face_roi(img_ds, is_dataset=True)
    roi_live = extract_face_roi(img_live, is_dataset=False)

    # 🛑 STRICT NO FACE DETECTED CHECK 🛑
    if roi_live is None:
        return {
            "status": "success",
            "matched": False,
            "face_detected": False,
            "similarity": 0.0,
            "engine": "Dlib HOG / OpenCV Face Detector AI",
            "message": "TIDAK TERDETEKSI WAJAH DI KAMERA. Silakan posisikan wajah di depan kamera."
        }

    # Strategy A: face_recognition (128-d ResNet embedding) if available
    if FACE_REC_AVAILABLE:
        try:
            rgb_ds = cv2.cvtColor(img_ds, cv2.COLOR_BGR2RGB)
            rgb_live = cv2.cvtColor(img_live, cv2.COLOR_BGR2RGB)

            enc_ds = face_recognition.face_encodings(rgb_ds)
            enc_live = face_recognition.face_encodings(rgb_live)
            enc_live_flip = face_recognition.face_encodings(cv2.flip(rgb_live, 1))

            all_live_encs = enc_live + enc_live_flip

            if len(enc_ds) > 0 and len(all_live_encs) > 0:
                distances = face_recognition.face_distance(all_live_encs, enc_ds[0])
                min_dist = float(min(distances))

                # Dlib 128-d threshold: dist <= 0.62 is SAME person (handles age variation 3-5 years)
                if min_dist <= 0.62:
                    similarity = round(float(max(78.0, min(99.0, (1.0 - min_dist) * 100.0 + 12.0))), 1)
                    return {
                        "status": "success",
                        "matched": True,
                        "face_detected": True,
                        "similarity": similarity,
                        "dlib_distance": round(min_dist, 3),
                        "engine": "Dlib 128-d ResNet Deep Learning AI",
                        "message": "Wajah COCOK dengan dataset terdaftar! Presensi Lolos."
                    }
                else:
                    similarity = round(float(max(15.0, (1.0 - min_dist) * 100.0)), 1)
                    return {
                        "status": "success",
                        "matched": False,
                        "face_detected": True,
                        "similarity": similarity,
                        "dlib_distance": round(min_dist, 3),
                        "engine": "Dlib 128-d ResNet Deep Learning AI",
                        "message": "Wajah ORANG LAIN / TIDAK COCOK dengan dataset terdaftar."
                    }
        except Exception:
            pass

    # Strategy B: LBPH Face Recognizer with Invariance
    recognizer = cv2.face.LBPHFaceRecognizer_create(radius=1, neighbors=8, grid_x=8, grid_y=8)
    recognizer.train([roi_ds], np.array([1]))

    roi_live_flip = cv2.flip(roi_live, 1)
    roi_live_scale_up = cv2.resize(roi_live[6:122, 6:122], (128, 128))
    roi_live_scale_down = cv2.copyMakeBorder(cv2.resize(roi_live, (112, 112)), 8, 8, 8, 8, cv2.BORDER_REPLICATE)

    variations = [roi_live, roi_live_flip, roi_live_scale_up, roi_live_scale_down]

    min_lbph_dist = 999.0
    for var in variations:
        _, dist = recognizer.predict(var)
        if dist < min_lbph_dist:
            min_lbph_dist = float(dist)

    # LBPH Distance Thresholding:
    # min_lbph_dist <= 72.0 -> SAME PERSON (PASSED)
    # min_lbph_dist > 72.0  -> DIFFERENT PERSON (REJECTED)
    if min_lbph_dist <= 72.0:
        score = 99.0 - (min_lbph_dist * 0.30)
        percentage = round(float(max(78.0, min(99.0, score))), 1)
        is_matched = True
        msg = "Wajah COCOK dengan dataset terdaftar! Presensi Lolos."
    else:
        score = 62.0 - ((min_lbph_dist - 72.0) * 0.6)
        percentage = round(float(max(15.0, score)), 1)
        is_matched = False
        msg = "Wajah ORANG LAIN / TIDAK COCOK dengan dataset terdaftar."

    engine_name = "Dlib HOG + LBPH Invariant AI" if DLIB_AVAILABLE else "OpenCV Invariant AI"

    return {
        "status": "success",
        "matched": is_matched,
        "face_detected": True,
        "similarity": percentage,
        "lbph_distance": round(min_lbph_dist, 1),
        "engine": engine_name,
        "message": msg
    }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "matched": False,
            "face_detected": False,
            "similarity": 0.0,
            "message": "Penggunaan: python face_verify.py <foto_dataset> <foto_live>"
        }))
        sys.exit(1)

    path_dataset = sys.argv[1]
    path_live = sys.argv[2]

    res = verify_face_strict(path_dataset, path_live)
    print(json.dumps(res))

if __name__ == "__main__":
    main()
