import os
import requests
import re

# Configuration
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PUBLIC_DIR = os.path.join(BASE_DIR, 'public')
ASSETS_DIR = os.path.join(PUBLIC_DIR, 'assets', 'vendor')

# URLs
TAILWIND_URL = "https://cdn.tailwindcss.com"
SWEETALERT2_URL = "https://cdn.jsdelivr.net/npm/sweetalert2@11"
SOCKET_IO_URL = "https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.2/socket.io.js"
FONT_CSS_URL = "https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap"

def ensure_dir(path):
    if not os.path.exists(path):
        os.makedirs(path)

def download_file(url, path):
    print(f"Downloading {url} to {path}")
    try:
        response = requests.get(url)
        response.raise_for_status()
        with open(path, 'wb') as f:
            f.write(response.content)
        print("Success.")
    except Exception as e:
        print(f"Error downloading {url}: {e}")

def download_fonts():
    print("Processing Fonts...")
    fonts_dir = os.path.join(ASSETS_DIR, 'fonts')
    ensure_dir(fonts_dir)

    # 1. Get CSS
    try:
        # User agent to get woff2 format
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        response = requests.get(FONT_CSS_URL, headers=headers)
        response.raise_for_status()
        css_content = response.text
    except Exception as e:
        print(f"Error fetching font CSS: {e}")
        return

    # 2. Parse and download font files
    # Look for url(https://...)
    font_urls = re.findall(r'url\((https://[^)]+)\)', css_content)
    
    mapping = {}
    
    for url in font_urls:
        filename = url.split('/')[-1]
        local_path = os.path.join(fonts_dir, filename)
        download_file(url, local_path)
        mapping[url] = filename

    # 3. Rewrite CSS
    for url, filename in mapping.items():
        css_content = css_content.replace(url, filename)

    # 4. Save CSS
    css_path = os.path.join(ASSETS_DIR, 'css', 'prompt.css')
    ensure_dir(os.path.dirname(css_path))
    with open(css_path, 'w', encoding='utf-8') as f:
        f.write(css_content)
    print(f"Saved font CSS to {css_path}")

def main():
    ensure_dir(ASSETS_DIR)
    
    # Tailwind
    tailwind_dir = os.path.join(ASSETS_DIR, 'tailwind')
    ensure_dir(tailwind_dir)
    download_file(TAILWIND_URL, os.path.join(tailwind_dir, 'tailwind.js'))

    # SweetAlert2
    swal_dir = os.path.join(ASSETS_DIR, 'sweetalert2')
    ensure_dir(swal_dir)
    download_file(SWEETALERT2_URL, os.path.join(swal_dir, 'sweetalert2.js'))

    # Socket.IO
    socket_dir = os.path.join(ASSETS_DIR, 'socket.io')
    ensure_dir(socket_dir)
    download_file(SOCKET_IO_URL, os.path.join(socket_dir, 'socket.io.js'))

    # Fonts
    download_fonts()

    # Audio
    audio_dir = os.path.join(ASSETS_DIR, 'audio')
    ensure_dir(audio_dir)
    download_file("https://assets.mixkit.co/sfx/preview/mixkit-software-interface-start-2574.mp3", os.path.join(audio_dir, 'success.mp3'))
    download_file("https://assets.mixkit.co/sfx/preview/mixkit-wrong-answer-fail-notification-946.mp3", os.path.join(audio_dir, 'error.mp3'))

if __name__ == "__main__":
    main()
