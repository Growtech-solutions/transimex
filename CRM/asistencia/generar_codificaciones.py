import face_recognition
import numpy as np
import os

# Carpeta con fotos de trabajadores
WORKERS_FOLDER = "/var/www/transimex/documentos/RecursosHumanos/fotos_trabajadores"
# Archivos de salida
ENCODINGS_FILE = "/var/www/transimex/CRM/asistencia/face_data/encodings.npy"
IDS_FILE = "/var/www/transimex/CRM/asistencia/face_data/ids.npy"

known_faces = []
known_ids = []

print("Generando codificaciones...")

for filename in os.listdir(WORKERS_FOLDER):
    if filename.endswith(".jpg") or filename.endswith(".png"):
        worker_id = filename.split(".")[0]
        image_path = os.path.join(WORKERS_FOLDER, filename)
        print(f"Procesando {filename}...")

        image = face_recognition.load_image_file(image_path)
        encodings = face_recognition.face_encodings(image)

        if encodings:
            known_faces.append(encodings[0])
            known_ids.append(worker_id)
        else:
            print(f"[ADVERTENCIA] No se detectó rostro en: {filename}")

# Guardar los vectores
np.save(ENCODINGS_FILE, known_faces)
np.save(IDS_FILE, known_ids)

print("✔ Codificaciones guardadas con éxito.")
