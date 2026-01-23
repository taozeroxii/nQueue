from flask import Flask, render_template, jsonify, request
from flask_socketio import SocketIO, emit
import requests
import logging
from datetime import datetime
import json
import os
from threading import Lock

app = Flask(__name__)
app.config['SECRET_KEY'] = 'nqueue_secret_key'
socketio = SocketIO(app, cors_allowed_origins="*", async_mode='threading')

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class TTSService:
    def __init__(self):
        self.google_tts_url = "https://translate.google.com/translate_tts"
    
    def generate_speech_url(self, text, lang='th'):
        """Generate Google Translate TTS URL for Thai female voice"""
        params = {
            'ie': 'UTF-8',
            'q': text,
            'tl': lang,
            'client': 'tw-ob',
            'textlen': len(text)
        }
        return f"{self.google_tts_url}?ie=UTF-8&q={requests.utils.quote(text)}&tl={lang}&client=tw-ob"
    
    def get_speech_audio(self, text, lang='th'):
        """Get speech audio from Google Translate TTS"""
        try:
            response = requests.get(
                self.google_tts_url,
                params={
                    'ie': 'UTF-8',
                    'q': text,
                    'tl': lang,
                    'client': 'tw-ob'
                },
                timeout=10
            )
            
            if response.status_code == 200:
                return response.content
            else:
                logger.error(f"TTS request failed with status {response.status_code}")
                return None
        except Exception as e:
            logger.error(f"TTS error: {e}")
            return None

tts_service = TTSService()

# Mock data for testing
mock_rooms = [
    {'id': 1, 'room_name': '1', 'department': 'อายุรกรรม', 'description': 'ตรวจทั่วไป'},
    {'id': 2, 'room_name': '2', 'department': 'อายุรกรรม', 'description': 'ตรวจพิเศษ'},
    {'id': 3, 'room_name': '3', 'department': 'ทันตกรรม', 'description': 'ทันตกรรมทั่วไป'}
]

mock_queues = [
    {'id': 1, 'vn': 'A001', 'oqueue': '001', 'patient_name': 'สมชาย ใจดี', 'room_number': 1, 'status': 'called', 'display_order': 1, 'created_at': datetime.now().isoformat(), 'updated_at': datetime.now().isoformat()},
    {'id': 2, 'vn': 'A002', 'oqueue': '002', 'patient_name': 'สมศรี รักดี', 'room_number': 1, 'status': 'waiting', 'display_order': 2, 'created_at': datetime.now().isoformat(), 'updated_at': datetime.now().isoformat()},
    {'id': 3, 'vn': 'B001', 'oqueue': '003', 'patient_name': 'วิชัย มีชัย', 'room_number': 2, 'status': 'waiting', 'display_order': 1, 'created_at': datetime.now().isoformat(), 'updated_at': datetime.now().isoformat()},
    {'id': 4, 'vn': 'C001', 'oqueue': '004', 'patient_name': 'มานี มีสุข', 'room_number': 3, 'status': 'lab', 'display_order': 1, 'created_at': datetime.now().isoformat(), 'updated_at': datetime.now().isoformat()},
    {'id': 5, 'vn': 'C002', 'oqueue': '005', 'patient_name': 'ประสิทธิ์ มีโชค', 'room_number': 3, 'status': 'xray', 'display_order': 2, 'created_at': datetime.now().isoformat(), 'updated_at': datetime.now().isoformat()}
]

# API Routes
@app.route('/api/departments')
def get_departments():
    try:
        departments = ['อายุรกรรม', 'ทันตกรรม', 'ศัลยกรรม']
        return jsonify({'success': True, 'data': departments})
    except Exception as e:
        logger.error(f"Error fetching departments: {e}")
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/rooms')
def get_rooms():
    try:
        department = request.args.get('department')
        rooms = mock_rooms
        if department:
            rooms = [r for r in rooms if r['department'] == department]
        return jsonify({'success': True, 'data': rooms})
    except Exception as e:
        logger.error(f"Error fetching rooms: {e}")
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/queue_data')
def get_queue_data():
    try:
        room = request.args.get('room')
        limit = request.args.get('limit', 50)
        department = request.args.get('department')
        
        queues = mock_queues
        
        if room:
            queues = [q for q in queues if str(q['room_number']) == room]
        
        if department:
            dept_rooms = [r['id'] for r in mock_rooms if r['department'] == department]
            queues = [q for q in queues if q['room_number'] in dept_rooms]
        
        return jsonify({'success': True, 'data': queues})
    except Exception as e:
        logger.error(f"Error fetching queue data: {e}")
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/settings')
def get_settings():
    try:
        settings = {
            'dept_name': 'คิวตรวจโรคทั่วไป',
            'dept_sub': 'General OPD Queue'
        }
        return jsonify({'success': True, 'data': settings})
    except Exception as e:
        logger.error(f"Error fetching settings: {e}")
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/tts_url')
def get_tts_url():
    try:
        text = request.args.get('text')
        if not text:
            return jsonify({'success': False, 'message': 'Text parameter is required'})
        
        tts_url = tts_service.generate_speech_url(text)
        return jsonify({'success': True, 'url': tts_url})
    except Exception as e:
        logger.error(f"Error generating TTS URL: {e}")
        return jsonify({'success': False, 'message': str(e)})

# WebSocket events
@socketio.on('connect')
def handle_connect():
    logger.info('Client connected')
    emit('status', {'msg': 'Connected to nQueue Display System'})

@socketio.on('disconnect')
def handle_disconnect():
    logger.info('Client disconnected')

@socketio.on('recall')
def handle_recall(data):
    # Broadcast recall event to all connected clients
    emit('recall', {'event': 'recall', 'data': data}, broadcast=True)

# Main display route
@app.route('/')
def multipledisplay():
    return render_template('multipledisplay.html')

if __name__ == '__main__':
    socketio.run(app, host='0.0.0.0', port=5000, debug=True)
