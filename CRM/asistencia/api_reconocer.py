from flask import Flask, request, jsonify
import face_recognition
import numpy as np
import base64
import io
from PIL import Image

app = Flask(__name__)

# Cargar encodings al inicio
known_faces = np.load("/var/www/transimex/CRM/asistencia/face_data/encodings.npy", allow_pickle=True)
known_ids = np.load("/var/www/transimex/CRM/asistencia/face_data/ids.npy", allow_pickle=True)

@app.route('/reconocer', methods=['POST'])
def reconocer():
    data = request.get_json()
    if not data or 'image' not in data:
        return jsonify({'error': 'No se recibió imagen'}), 400

    try:
        image_data = data['image']
        # Decodificar base64
        image_bytes = base64.b64decode(image_data.split(",")[1])
        image = Image.open(io.BytesIO(image_bytes))
        rgb_image = np.array(image.convert('RGB'))

        faces = face_recognition.face_locations(rgb_image)
        if not faces:
            return jsonify({'id': None, 'mensaje': 'Desconocido'})

        encodings = face_recognition.face_encodings(rgb_image, faces)
        rostro = encodings[0]

        matches = face_recognition.compare_faces(known_faces, rostro, tolerance=0.5)
        distances = face_recognition.face_distance(known_faces, rostro)

        if True in matches:
            best_match = np.argmin(distances)
            return jsonify({'id': known_ids[best_match].item(), 'mensaje': 'Reconocido'})
        else:
            return jsonify({'id': None, 'mensaje': 'Desconocido'})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000)
