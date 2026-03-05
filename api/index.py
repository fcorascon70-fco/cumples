import json
import pymysql
from http.server import BaseHTTPRequestHandler
from urllib.parse import urlparse, parse_qs

class handler(BaseHTTPRequestHandler):
    def do_OPTIONS(self):
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()

    def do_GET(self):
        self.handle_request()

    def do_POST(self):
        self.handle_request()

    def handle_request(self):
        query = parse_qs(urlparse(self.path).query)
        action = query.get('action', [None])[0]

        config = {
            'host': '162.216.5.50',
            'user': 'directac_fco',
            'password': '**xmiswebs**',
            'database': 'directac_RNM_2025',
            'charset': 'utf8mb4',
            'cursorclass': pymysql.cursors.DictCursor,
            'connect_timeout': 5
        }

        response_data = {"error": "Acción no válida"}
        status_code = 200

        try:
            conn = pymysql.connect(**config)
            with conn.cursor() as cursor:
                if action == 'ping':
                    response_data = {"success": True, "message": "API está viva y conectada a la base de datos"}

                elif action == 'login':
                    length = int(self.headers.get('content-length', 0))
                    data = json.loads(self.rfile.read(length))
                    usuario = data.get('username')
                    password = data.get('password')
                    cursor.execute("SELECT usuario FROM usuarios WHERE usuario = %s AND password = %s", (usuario, password))
                    user = cursor.fetchone()
                    if user:
                        response_data = {"success": True, "user": user['usuario']}
                    else:
                        response_data = {"success": False, "error": "Usuario o contraseña incorrectos"}

                elif action == 'get_months':
                    cursor.execute("SELECT mesid, mes FROM mes ORDER BY mesid ASC")
                    response_data = cursor.fetchall()

                elif action == 'get_days':
                    cursor.execute("SELECT dia FROM dias ORDER BY dia ASC")
                    response_data = cursor.fetchall()

                elif action == 'search_miembros':
                    mes = query.get('mes', [None])[0]
                    dia = query.get('dia', [None])[0]
                    cursor.execute("SELECT nombre_completo, dia, celular, email FROM miembros WHERE mes = %s AND dia = %s ORDER BY nombre_completo ASC", (mes, dia))
                    response_data = cursor.fetchall()


            conn.close()
        except Exception as e:
            status_code = 500
            response_data = {"success": False, "error": str(e)}

        self.send_response(status_code)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Access-Control-Allow-Origin', '*')
        self.end_headers()
        self.wfile.write(json.dumps(response_data, default=str).encode('utf-8'))
