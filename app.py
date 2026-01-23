from flask import Flask, render_template, jsonify, request
from flask_socketio import SocketIO, emit
import mysql.connector
from mysql.connector import Error
import requests
import logging
from datetime import datetime
import json
import os
from threading import Lock

app = Flask(__name__)
app.config['SECRET_KEY'] = 'nqueue_secret_key'
socketio = SocketIO(app, cors_allowed_origins="*", async_mode='threading')

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'database': 'nqueue',
    'user': 'root',
    'password': '',
    'port': 3306
}

# Thread lock for database operations
db_lock = Lock()

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class Database:
    def __init__(self):
        self.connection = None
        self.connect()
    
    def connect(self):
        try:
            self.connection = mysql.connector.connect(**DB_CONFIG)
            if self.connection.is_connected():
                logger.info("Connected to MySQL database")
        except Error as e:
            logger.error(f"Error connecting to MySQL: {e}")
    
    def get_connection(self):
        if not self.connection or not self.connection.is_connected():
            self.connect()
        return self.connection
    
    def execute_query(self, query, params=None):
        with db_lock:
            conn = self.get_connection()
            cursor = conn.cursor(dictionary=True)
            try:
                if params:
                    cursor.execute(query, params)
                else:
                    cursor.execute(query)
                
                if query.strip().upper().startswith('SELECT'):
                    result = cursor.fetchall()
                    return result
                else:
                    conn.commit()
                    return cursor.lastrowid
            except Error as e:
                logger.error(f"Query error: {e}")
                return None
            finally:
                cursor.close()

# Initialize database
db = Database()

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

# API Routes
@app.route('/api/departments')
def get_departments():
    try:
        query = "SELECT DISTINCT department FROM rooms WHERE department IS NOT NULL"
        departments = db.execute_query(query)
        dept_list = [dept['department'] for dept in departments] if departments else []
        return jsonify({'success': True, 'data': dept_list})
    except Exception as e:
        logger.error(f"Error fetching departments: {e}")
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/rooms')
def get_rooms():
    try:
        department = request.args.get('department')
        query = "SELECT id, room_name, department, description FROM rooms"
        params = []
        
        if department:
            query += " WHERE department = %s"
            params.append(department)
        
        query += " ORDER BY room_name ASC"
        
        rooms = db.execute_query(query, tuple(params) if params else None)
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
        
        where_conditions = ["DATE(created_at) = CURDATE()"]
        params = []
        
        if room:
            where_conditions.append("room_number = %s")
            params.append(room)
        
        if department:
            where_conditions.append("room_number IN (SELECT id FROM rooms WHERE department = %s)")
            params.append(department)
        
        query = f"""
            SELECT * FROM queues 
            WHERE {' AND '.join(where_conditions)}
            ORDER BY room_number ASC, display_order ASC, id ASC 
            LIMIT %s
        """
        params.append(limit)
        
        queues = db.execute_query(query, tuple(params))
        return jsonify({'success': True, 'data': queues})
    except Exception as e:
        logger.error(f"Error fetching queue data: {e}")
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/settings')
def get_settings():
    try:
        # Return default settings - can be enhanced to fetch from database
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
