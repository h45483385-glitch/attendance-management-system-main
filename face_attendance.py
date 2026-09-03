import cv2
import face_recognition
import numpy as np
import requests
import time
import os
import json

# -------------------------
# LARAVEL API
# -------------------------
API_URL = "http://127.0.0.1:8000/api/face-attendance"

# -------------------------
# LOAD KNOWN FACES
# -------------------------
known_face_encodings = []
known_face_ids = []

DATA_FILE = "faces_db.json"

if not os.path.exists(DATA_FILE):
    print(f"❌ Face database not found: {DATA_FILE}. Please register faces first.")
    exit()

with open(DATA_FILE, "r") as f:
    known_faces = json.load(f)

for emp_id_str, encoding in known_faces.items():
    known_face_encodings.append(np.array(encoding))
    known_face_ids.append(emp_id_str)

if len(known_face_encodings) == 0:
    print("❌ No faces found in the database. Please register faces first.")
    exit()

# -------------------------
# CAMERA
# -------------------------
cam = cv2.VideoCapture(0)

if not cam.isOpened():
    print("❌ Camera not accessible")
    exit()

print("✅ Face Attendance System Started")
print("Press 'q' to quit")

# -------------------------
# CONTROL VARIABLES
# -------------------------
processed_faces = {}
COOLDOWN = 10
GLOBAL_COOLDOWN = 5
last_global_call = 0   # FIXED (lowercase, usable variable)

# -------------------------
# LOOP
# -------------------------
while True:

    ret, frame = cam.read()
    if not ret:
        break

    rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)

    face_locations = face_recognition.face_locations(rgb)
    face_encodings = face_recognition.face_encodings(rgb, face_locations)

    for (top, right, bottom, left), face_encoding in zip(face_locations, face_encodings):

        name = "Unknown"
        emp_id = None

        # -------------------------
        # FACE MATCHING
        # -------------------------
        if len(known_face_encodings) > 0:
            face_distances = face_recognition.face_distance(known_face_encodings, face_encoding)
            best_match_index = np.argmin(face_distances)

            if face_distances[best_match_index] < 0.5:
                emp_id = known_face_ids[best_match_index]
                name = f"Emp {emp_id}"

        # -------------------------
        # API CALL CONTROL
        # -------------------------
        if emp_id is not None:

            current_time = time.time()
            last_time = processed_faces.get(emp_id, 0)

            if (current_time - last_time > COOLDOWN) and (current_time - last_global_call > GLOBAL_COOLDOWN):

                try:
                    print(f"📡 Sending attendance for: {emp_id}")

                    response = requests.post(
                        API_URL,
                        json={"emp_id": emp_id},
                        timeout=5
                    )

                    try:
                        data = response.json()
                        print("Response:", data)
                    except:
                        print("⚠️ Non-JSON Response:", response.text)
                        continue

                    # ✅ TRUE SUCCESS ONLY
                    if response.status_code == 200 and data.get("status") is True:
                        processed_faces[emp_id] = current_time
                        last_global_call = current_time
                        print("✅ Attendance marked")

                    # 🚫 BLOCK AFTER COMPLETE
                    elif data.get("message") == "Already completed IN & OUT for today":
                        processed_faces[emp_id] = current_time + 99999
                        print("⛔ Already completed - stopping further calls")

                    else:
                        print("⚠️ Not marked:", data.get("message"))

                except Exception as e:
                    print("❌ Request failed:", e)

        # -------------------------
        # DRAW UI
        # -------------------------
        color = (0, 255, 0) if emp_id else (0, 0, 255)

        cv2.rectangle(frame, (left, top), (right, bottom), color, 2)
        cv2.putText(frame, name, (left, top - 10),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 2)

    # -------------------------
    # HEADER TEXT
    # -------------------------
    cv2.putText(frame, "Face Attendance System", (20, 40),
                cv2.FONT_HERSHEY_SIMPLEX, 0.8, (255, 255, 0), 2)

    cv2.imshow("Live Face Attendance", frame)

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cam.release()
cv2.destroyAllWindows()