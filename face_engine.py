import cv2
import face_recognition
import numpy as np
import requests
import os
import time

# -----------------------------
# 🔹 CONFIG
# -----------------------------
API_URL = "http://127.0.0.1:8000/api/face-attendance"
COOLDOWN = 5  # seconds to avoid duplicate API calls
LAST_SENT = {}

# -----------------------------
# 🔹 LOAD KNOWN FACES
# -----------------------------
known_face_encodings = []
known_face_ids = []

FACE_DIR = "faces"

print("📂 Loading known faces...")

for file in os.listdir(FACE_DIR):
    if file.endswith(".jpg") or file.endswith(".png"):
        path = os.path.join(FACE_DIR, file)

        try:
            image = face_recognition.load_image_file(path)
            encodings = face_recognition.face_encodings(image)

            if len(encodings) == 0:
                print(f"⚠ No face found in {file}")
                continue

            encoding = encodings[0]

            emp_id = os.path.splitext(file)[0]  # 111.jpg → 111

            known_face_encodings.append(encoding)
            known_face_ids.append(emp_id)

            print(f"✅ Loaded face: {emp_id}")

        except Exception as e:
            print(f"❌ Error loading {file}: {e}")

# -----------------------------
# 🔹 START CAMERA
# -----------------------------
video = cv2.VideoCapture(0)

if not video.isOpened():
    print("❌ Camera not accessible")
    exit()

print("📸 Face Engine Started...")
print("Press 'q' to quit")

# -----------------------------
# 🔹 MAIN LOOP
# -----------------------------
while True:

    ret, frame = video.read()
    if not ret:
        print("❌ Frame not received")
        break

    # Resize for faster processing
    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb = cv2.cvtColor(small_frame, cv2.COLOR_BGR2RGB)

    face_locations = face_recognition.face_locations(rgb)
    face_encodings = face_recognition.face_encodings(rgb, face_locations)

    for (top, right, bottom, left), encoding in zip(face_locations, face_encodings):

        matches = face_recognition.compare_faces(known_face_encodings, encoding)
        face_distances = face_recognition.face_distance(known_face_encodings, encoding)

        emp_id = None

        if len(face_distances) > 0:
            best_match_index = np.argmin(face_distances)

            if matches[best_match_index]:
                emp_id = known_face_ids[best_match_index]

        # Scale back coordinates
        top *= 4
        right *= 4
        bottom *= 4
        left *= 4

        # -----------------------------
        # 🔹 Draw UI
        # -----------------------------
        label = "Unknown"

        if emp_id:
            label = f"ID: {emp_id}"

            cv2.putText(frame, "Recognized", (left, top - 10),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

            # -----------------------------
            # 🔹 API CALL WITH COOLDOWN
            # -----------------------------
            now = time.time()

            if emp_id not in LAST_SENT or (now - LAST_SENT[emp_id] > COOLDOWN):

                try:
                    print(f"📡 Sending attendance for {emp_id}")

                    response = requests.post(
                        API_URL,
                        json={"emp_id": emp_id},
                        headers={
                            "Accept": "application/json",
                            "Content-Type": "application/json"
                        },
                        timeout=5
                    )

                    print("📡 Response:", response.json())

                    LAST_SENT[emp_id] = now

                except Exception as e:
                    print("❌ API Error:", e)

        # Draw bounding box
        cv2.rectangle(frame, (left, top), (right, bottom), (0, 255, 0), 2)
        cv2.putText(frame, label, (left, bottom + 25),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)

    cv2.imshow("Face Engine", frame)

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

video.release()
cv2.destroyAllWindows()