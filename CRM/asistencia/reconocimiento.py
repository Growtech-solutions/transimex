import face_recognition
import numpy as np
import sys

# Cargar vectores ya procesados
known_faces = np.load("/var/www/transimex/CRM/asistencia/face_data/encodings.npy", allow_pickle=True)
known_ids = np.load("/var/www/transimex/CRM/asistencia/face_data/ids.npy", allow_pickle=True)

# Imagen capturada
image_path = sys.argv[1]
imagen = face_recognition.load_image_file(image_path)
codificaciones = face_recognition.face_encodings(imagen)

if codificaciones:
    rostro = codificaciones[0]
    matches = face_recognition.compare_faces(known_faces, rostro, tolerance=0.5)
    distancias = face_recognition.face_distance(known_faces, rostro)

    if True in matches:
        mejor = np.argmin(distancias)
        print(known_ids[mejor])
    else:
        print("Desconocido")
else:
    print("Desconocido")
