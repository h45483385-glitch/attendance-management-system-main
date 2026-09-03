from fastapi 
import FastAPI, File, UploadFile
import face_recognition
import numpy as np
import uvicorn
import os
import json
import requests


app = FastAPI()

# -------------------------
# FILE STORAGE (PERSISTENT)
# -------------------------
DATA_FILE = "faces_db.json"

def load_faces():
    if os.path.exists(DATA_FILE):
        with open(DATA_FILE, "r") as f:
            return json.load(f)
    return {}

def save_faces(data):
    with open(DATA_FILE, "w") as f:
        json.dump(data, f)

known_faces = load_faces()

# -------------------------
# REGISTER FACE
# -------------------------
@app.post("/register-face")
async def register_face(emp_id: str, file: UploadFile = File(...)):

    image = face_recognition.load_image_file(file.file)
    encodings = face_recognition.face_encodings(image)

    if len(encodings) == 0:
        return {"status": False, "message": "No face detected"}

    known_faces[emp_id] = encodings[0].tolist()
    save_faces(known_faces)

    return {
        "status": True,
        "message": "Face registered successfully",
        "emp_id": emp_id
    }

# -------------------------
# MATCH FACE
# -------------------------
@app.post("/match-face")
async def match_face(file: UploadFile = File(...)):

    image = face_recognition.load_image_file(file.file)
    encodings = face_recognition.face_encodings(image)

    if len(encodings) == 0:
        return {"status": False, "message": "No face detected"}

    input_encoding = encodings[0]

    best_match = None
    best_score = 0.6  # tolerance threshold

    for emp_id, stored_encoding in known_faces.items():

        results = face_recognition.compare_faces(
            [np.array(stored_encoding)],
            input_encoding,
            tolerance=0.5
        )

        face_distance = face_recognition.face_distance(
            [np.array(stored_encoding)],
            input_encoding
        )[0]

        if results[0] and face_distance < best_score:
            best_match = emp_id
            best_score = face_distance

    if best_match:
        # --- THE API MAPPING FOR THRISH ---
        # This tells Laravel to update the phpMyAdmin database
        try:
            laravel_url = "http://127.0.0.1:8000/api/attendance/store" 
            payload = {"emp_id": best_match}
            # Sending the data to your AttendanceController
            requests.post(laravel_url, json=payload) 
        except Exception as e:
            print(f"Database API mapping failed: {e}")
        # ----------------------------------

        return {
            "status": True,
            "emp_id": best_match,
            "confidence": float(best_score),
            "message": "Face matched and attendance logged!"
        }
        return {
            "status": True,
            "emp_id": best_match,
            "confidence": float(best_score),
            "message": "Face matched"
        }

    return {
        "status": False,
        "message": "No match found"
    }

# -------------------------
# RUN SERVER
# -------------------------
if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8001)