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

app = Flask(__name__)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_VERSION = 1

ALLOWED_EXT = set(['jpg' , 'jpeg' , 'png'])
def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1] in ALLOWED_EXT

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

        if (confidence > 0.8):
            frame = image[y1:y2, x1:x2]
            if (frame.size != 0):
                images.append(frame)
    return images
            
def generate(filename):
    img_size = (64, 64)
    num_channels = 1
    img = extract_face(filename)
    embedding = []
    for i in img:
        i = cv2.cvtColor(i, cv2.COLOR_BGR2GRAY)
        #cv2.imwrite("test.jpg", i)
        i = cv2.resize(i, img_size)
        i = np.array(i, dtype=np.float32) / 255.0
        i = i.reshape(1, img_size[0], img_size[1], num_channels)
        with open(f"models/cnn-25-1024-sigmoid-binaryce.pkl", "rb") as f:
            model = pickle.load(f)
            model.predict(i)
            embedding.append(model.predict(i).tolist()[0][0][0])
    os.remove(filename)
    return embedding, len(embedding)

def AP_Clustering(data_2d):
    affinity_propagation = AffinityPropagation(random_state=0)
    affinity_propagation.fit(data_2d)
    print(data_2d.shape)
    labels = affinity_propagation.predict(data_2d)
    return labels

@app.route('/')
def home():
        return jsonify({'message' : 'API Live with Model Version {}'.format(MODEL_VERSION)})

@app.route('/cluster' , methods = ['GET' , 'POST'])
def cluster():
    if request.method == 'POST':
        embeddings = request.json['embeddings']
        embeddings = np.array(embeddings)
        labels = AP_Clustering(embeddings)
        return jsonify({'names': request.json['names'], 'labels': labels.tolist()}) 
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

                embedding, count = generate(img_path)
            else:
                error = "Please upload a valid image file"

            if(len(error) == 0):
                return jsonify({'name' : img , 'count': count, 'embeddings' : embedding})
            else:
                return jsonify({'error' : error})
    else:
        return jsonify({'error' : 'POST request not found'})

if __name__ == "__main__":
    app.run(debug = True)