#!/usr/bin/env python3
"""
ImportWale AI Visual Feature Embedding Microservice
Extracts dense 128-dimensional visual feature vectors (shape, color distribution, edge gradients)
and calculates Cosine Similarity scores. Supports both HTTP Microservice and CLI execution modes.
"""

import sys
import os
import io
import json
import argparse
import urllib.request
import urllib.parse
import requests
import numpy as np
from PIL import Image

def load_pil_image(image_source):
    """
    Robust image loader supporting:
    - HTTP / HTTPS URLs
    - Absolute & relative local file paths
    - File storage objects / bytes / stream
    """
    try:
        if isinstance(image_source, str):
            image_source = image_source.strip()
            # 1. Remote HTTP / HTTPS URL
            if image_source.startswith(('http://', 'https://')):
                headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'}
                try:
                    resp = requests.get(image_source, headers=headers, timeout=10)
                    if resp.status_code == 200 and resp.content:
                        return Image.open(io.BytesIO(resp.content)).convert('RGB')
                    sys.stderr.write(f"[WARN] HTTP fetch failed ({resp.status_code}): {image_source}\n")
                except Exception as ex:
                    sys.stderr.write(f"[ERROR] HTTP request failed: {ex}\n")
                return None
            
            # 2. Local File Path
            if not os.path.isabs(image_source):
                # Try relative to script parent dir / web root
                script_dir = os.path.dirname(os.path.abspath(__file__))
                root_dir = os.path.dirname(script_dir)
                candidates = [
                    image_source,
                    os.path.join(root_dir, image_source),
                    os.path.join(root_dir, 'public', image_source.lstrip('/\\')),
                    os.path.join(root_dir, 'public', 'uploads', 'products', os.path.basename(image_source))
                ]
                for cand in candidates:
                    if os.path.exists(cand) and os.path.isfile(cand):
                        image_source = cand
                        break
            
            if os.path.exists(image_source):
                return Image.open(image_source).convert('RGB')
            else:
                sys.stderr.write(f"[WARN] Local image path does not exist: {image_source}\n")
                return None

        # 3. File storage stream or bytes
        if hasattr(image_source, 'read'):
            raw_bytes = image_source.read()
            if hasattr(image_source, 'seek'):
                image_source.seek(0)
            return Image.open(io.BytesIO(raw_bytes)).convert('RGB')

        if isinstance(image_source, (bytes, bytearray)):
            return Image.open(io.BytesIO(image_source)).convert('RGB')

    except Exception as e:
        sys.stderr.write(f"[ERROR] Failed to load image ({type(image_source)}): {e}\n")
        return None

    return None

def extract_visual_embedding(image_source):
    """
    Extract a normalized 128-dimensional visual embedding vector.
    Captures:
    - 4x4 Spatial Color Grid (RGB + Saturation + Brightness) -> 64 floats
    - 9x8 Luminance dHash Structure -> 32 floats
    - Spatial Edge Gradient Magnitude & Orientation Histograms -> 32 floats
    Total: 128 dimensions, L2-normalized.
    """
    img = load_pil_image(image_source)
    if img is None:
        return None

    try:
        # 1. 4x4 Spatial Grid Color Feature Vector (64 dimensions)
        grid_img = img.resize((4, 4), Image.Resampling.BILINEAR)
        grid_arr = np.array(grid_img, dtype=np.float32) / 255.0  # (4, 4, 3)
        
        color_features = []
        for y in range(4):
            for x in range(4):
                r, g, b = grid_arr[y, x]
                max_c = max(r, g, b)
                min_c = min(r, g, b)
                sat = (max_c - min_c) / (max_c + 1e-5)
                bright = (r + g + b) / 3.0
                color_features.extend([r, g, b, sat])
        color_features = np.array(color_features, dtype=np.float32)

        # 2. 9x8 Luminance dHash Structural Feature Vector (32 dimensions)
        dhash_img = img.resize((9, 8), Image.Resampling.BILINEAR).convert('L')
        dhash_arr = np.array(dhash_img, dtype=np.float32)
        diff = dhash_arr[:, 1:] > dhash_arr[:, :-1]  # (8, 8) boolean matrix
        dhash_features = np.where(diff.flatten(), 1.0, -1.0).astype(np.float32)
        dhash_features = (dhash_features[0::2] + dhash_features[1::2]) / 2.0

        # 3. Spatial Edge Gradient Histogram (32 dimensions)
        grad_img = img.resize((16, 16), Image.Resampling.BILINEAR).convert('L')
        grad_arr = np.array(grad_img, dtype=np.float32)
        gx = np.zeros_like(grad_arr)
        gy = np.zeros_like(grad_arr)
        gx[:, :-1] = grad_arr[:, 1:] - grad_arr[:, :-1]
        gy[:-1, :] = grad_arr[1:, :] - grad_arr[:-1, :]
        mag = np.sqrt(gx**2 + gy**2)
        
        mag_pooled = mag.reshape(4, 4, 4, 4).mean(axis=(1, 3)).flatten() / (mag.max() + 1e-5)
        angle = np.arctan2(gy, gx).flatten()
        angle_hist, _ = np.histogram(angle, bins=16, range=(-np.pi, np.pi))
        angle_hist = angle_hist.astype(np.float32) / (angle_hist.sum() + 1e-5)
        
        edge_features = np.concatenate([mag_pooled, angle_hist])

        # Combine feature vectors (64 + 32 + 32 = 128 dimensions)
        embedding = np.concatenate([color_features, dhash_features, edge_features])
        
        # L2 Normalization
        norm = np.linalg.norm(embedding)
        if norm > 0:
            embedding = embedding / norm

        return embedding.tolist()

    except Exception as e:
        sys.stderr.write(f"[ERROR] Embedding extraction failed: {e}\n")
        return None

def cosine_similarity(vec1, vec2):
    """Compute cosine similarity between two 128-dim vectors."""
    v1 = np.array(vec1, dtype=np.float32)
    v2 = np.array(vec2, dtype=np.float32)
    n1 = np.linalg.norm(v1)
    n2 = np.linalg.norm(v2)
    if n1 == 0 or n2 == 0:
        return 0.0
    return float(np.dot(v1, v2) / (n1 * n2))

# =========================================================================
#  HTTP MICROSERVICE SERVER (FLASK)
# =========================================================================
def run_server(port=5005):
    from flask import Flask, request, jsonify

    app = Flask(__name__)

    @app.route('/health', methods=['GET'])
    def health():
        return jsonify({'status': 'ok', 'service': 'visual_embedding_service', 'port': port})

    @app.route('/embed', methods=['POST'])
    def embed():
        image_path = None
        embedding = None

        if 'image' in request.files:
            file = request.files['image']
            embedding = extract_visual_embedding(file)
        elif request.is_json and 'image_path' in request.json:
            image_path = request.json['image_path']
            print(f"[*] Debug embed image_path received: {repr(image_path)}")
            embedding = extract_visual_embedding(image_path)
        elif 'image_path' in request.form:
            image_path = request.form['image_path']
            embedding = extract_visual_embedding(image_path)
        else:
            return jsonify({'success': False, 'message': 'No image file or image_path provided'}), 400

        if embedding is None:
            return jsonify({'success': False, 'message': 'Failed to process image'}), 400

        return jsonify({'success': True, 'embedding': embedding, 'dimensions': len(embedding)})

    @app.route('/similarity', methods=['POST'])
    def similarity():
        data = request.get_json() or {}
        query_vec = data.get('query_embedding')
        target_vecs = data.get('target_embeddings', [])

        if not query_vec or not target_vecs:
            return jsonify({'success': False, 'message': 'Missing query_embedding or target_embeddings'}), 400

        scores = [cosine_similarity(query_vec, t) for t in target_vecs]
        return jsonify({'success': True, 'scores': scores})

    print(f"[*] Starting Visual Embedding Microservice on http://127.0.0.1:{port}")
    app.run(host='127.0.0.1', port=port, debug=False)

# =========================================================================
#  CLI EXECUTION MODE
# =========================================================================
if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='ImportWale Visual Feature Embedding Microservice')
    parser.add_argument('--server', action='store_true', help='Run Flask HTTP Server')
    parser.add_argument('--port', type=int, default=5005, help='Port for HTTP server')
    parser.add_argument('--image', type=str, help='Image path or URL for CLI feature extraction')
    args = parser.parse_args()

    if args.server:
        run_server(args.port)
    elif args.image:
        embedding = extract_visual_embedding(args.image)
        if embedding is None:
            print(json.dumps({'success': False, 'message': 'Could not read or process image file'}))
            sys.exit(1)
        print(json.dumps({'success': True, 'embedding': embedding, 'dimensions': len(embedding)}))
    else:
        run_server(5005)
