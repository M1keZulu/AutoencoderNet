import os
import uuid
from flask import Flask, render_template, request
import urllib
import numpy as np
from PIL import Image
import pickle
from flask import jsonify
import cv2
from sklearn.cluster import AffinityPropagation
from sklearn.mixture import GaussianMixture
from sklearn.cluster import SpectralClustering
from sklearn.metrics import silhouette_score
import dlib
import math
import uuid
import base64

app = Flask(__name__)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_VERSION = "casia-cnn-128.pkl"

ALLOWED_EXT = set(['jpg' , 'jpeg' , 'png'])
def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1] in ALLOWED_EXT

def check_face(image):
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=1)
    extracted_faces = []
    for (x, y, w, h) in faces:
        face = image[y:y+h, x:x+w]
        extracted_faces.append(face)
    
    if len(extracted_faces) == 0:
        return False
    else:
        return True

def extract_face(filename):
    protoPath = os.path.join(BASE_DIR, "models/deploy.prototxt")
    caffePath = os.path.join(BASE_DIR, "models/weights.caffemodel")
    net = cv2.dnn.readNetFromCaffe(protoPath, caffePath)
    image = cv2.imread(filename)
    (h, w) = image.shape[:2]
    blob = cv2.dnn.blobFromImage(cv2.resize(image, (300, 300)), 1.0, (300, 300), (104.0, 177.0, 123.0))
    net.setInput(blob)
    detections = net.forward()
    images = []
    for i in range(0, detections.shape[2]):
        box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
        (x1, y1, x2, y2) = box.astype("int")
        confidence = detections[0, 0, i, 2]

        if (confidence > 0.1) and check_face(image):
            frame = image[y1:y2, x1:x2]
            if (frame.size != 0):
                images.append(frame)

    return images

def face_extraction_advanced(filename):
    img = cv2.imread(filename)

    detector = dlib.get_frontal_face_detector()
    predictor = dlib.shape_predictor("models/shape_predictor_68_face_landmarks.dat")

    faces = detector(img)

    images = []

    for face in faces:
        landmarks = predictor(img, face)

        points = []
        for i in range(68):
            x = landmarks.part(i).x
            y = landmarks.part(i).y
            points.append((x, y))

        center_x = sum([p[0] for p in points]) / len(points)
        center_y = sum([p[1] for p in points]) / len(points)

        dx = points[16][0] - points[0][0]
        dy = points[16][1] - points[0][1]
        angle = -1 * (180.0 / 3.14159) * (1.5708 - abs(math.atan2(dy, dx)))

        M = cv2.getRotationMatrix2D((center_x, center_y), angle+90, 1.1)
        aligned_face = cv2.warpAffine(img, M, img.shape[1::-1], flags=cv2.INTER_LINEAR)
        aligned_face = aligned_face[face.top():face.bottom(), face.left():face.right()]
        images.append(aligned_face)
    
    return images
            
def generate(filename):
    img_size = (64, 64)
    num_channels = 3
    img = face_extraction_advanced(filename)
    embedding = []
    images=[]
    for i in img:
        file_name = f"{uuid.uuid4()}.jpg"
        cv2.imwrite(file_name, i)
        i = cv2.resize(i, img_size)
        i = np.array(i, dtype=np.float32) / 255.0
        i = i.reshape(1, img_size[0], img_size[1], num_channels)
        with open(file_name, "rb") as image_file:
            encoded_string = base64.b64encode(image_file.read())
            images.append(encoded_string.decode('utf-8'))
        with open(f"models/{MODEL_VERSION}", "rb") as f:
            model = pickle.load(f)
            embedding.append(model.predict(i).tolist()[0])
    os.remove(filename)
    return embedding, len(embedding), images

def AP_Clustering(data_2d, user_labels=None):
    affinity_propagation = AffinityPropagation(max_iter=100000, random_state=0)
    affinity_propagation.fit(data_2d)
    labels = affinity_propagation.predict(data_2d)
    return labels

def spectral_clustering(data_2d, n_clusters=None):
    if not n_clusters:
        # Automatically determine the number of clusters
        sil_scores = []
        for n in range(3, len(data_2d)):
            spectral_clustering = SpectralClustering(n_clusters=n, affinity='nearest_neighbors', n_neighbors=2)
            labels = spectral_clustering.fit_predict(data_2d)
            sil_scores.append(silhouette_score(data_2d, labels))
        n_clusters = np.argmax(sil_scores)
        print("Number of clusters: ", n_clusters)
    spectral_clustering = SpectralClustering(n_clusters=n_clusters, affinity='nearest_neighbors', n_neighbors=2)
    labels = spectral_clustering.fit(data_2d)
    return labels.labels_


@app.route('/')
def home():
        return jsonify({'message' : 'API Live with Model Version {}'.format(MODEL_VERSION)})

@app.route('/cluster' , methods = ['GET' , 'POST'])
def cluster():
    if request.method == 'POST':
        embeddings = request.json['embeddings']
        #user_labels = request.json['labeled_data']
        embeddings = np.array(embeddings)
        labels = AP_Clustering(embeddings)
        return jsonify({'labels': labels.tolist()}) 
    else:
        return jsonify({'error' : 'POST request not found'})

@app.route('/success' , methods = ['GET' , 'POST'])
def success():
    error = ''
    target_img = os.path.join(os.getcwd() , 'static/images')
    if request.method == 'POST':    
        if (request.files):
            file = request.files['file']
            if file and allowed_file(file.filename):
                file.save(os.path.join(target_img , file.filename))
                img_path = os.path.join(target_img , file.filename)
                img = file.filename

                embedding, count, images = generate(img_path)
            else:
                error = "Please upload a valid image file"

            if(len(error) == 0):
                return jsonify({'name' : img , 'count': count, 'embeddings' : embedding, 'images' : images})
            else:
                return jsonify({'error' : error})
    else:
        return jsonify({'error' : 'POST request not found'})

if __name__ == "__main__":
    app.run(debug = True)